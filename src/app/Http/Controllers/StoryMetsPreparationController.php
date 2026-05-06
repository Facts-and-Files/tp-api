<?php

namespace App\Http\Controllers;

use App\Jobs\PrepareItemAltoJob;
use App\Models\Item;
use App\Models\Story;
use App\Services\Export\MetsStoryReadiness;
use App\Services\SendMetsReadyNotification;
use Illuminate\Bus\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\Request;

class StoryMetsPreparationController extends ResponseController
{
    public function __construct(
        private readonly MetsStoryReadiness $metsStoryReadiness,
        private readonly SendMetsReadyNotification $sendMetsReadyNotification,
    ) {}

    public function store(int $id, Request $request): JsonResponse
    {
        $story = Story::findOrFail($id);

        $rule = app()->environment('local')
            ? 'nullable|email:rfc'
            : 'nullable|email:rfc,dns';

        $notificationEmail = $request->validate([
            'notificationEmail' => $rule,
        ])['notificationEmail'] ?? null;

        if ($this->metsStoryReadiness->isReady($story)) {
            $this->sendMetsReadyNotification->send($notificationEmail, $story, 'ready', null);
            return $this->sendResponse([
                'status' => 'ready',
                'download_url' => url("/stories/{$id}/items/export/mets"),
            ], 'METS XML is ready', 200);
        }

        $allItems = $this->metsStoryReadiness->allItems($story);
        $missingItems = $this->metsStoryReadiness->missingItems($story);

        $jobs = $missingItems
            ->map(fn(Item $item) => new PrepareItemAltoJob($item))
            ->all();

        $batch = Bus::batch($jobs)
            ->name("mets-story-{$id}")
            ->allowFailures()
            ->finally(function (Batch $batch) use ($notificationEmail, $story) {
                $status = $this->resolveFinalBatchStatus($batch);
                $this->sendMetsReadyNotification->send($notificationEmail, $story, $status, $batch);
            })
            ->dispatch();

        return $this->sendResponse([
            'status'  => 'processing',
            'batch_id' => $batch->id,
            'status_url' => url("/stories/{$id}/items/exports/mets/status/{$batch->id}"),
            'download_url' => url("/stories/{$id}/items/export/mets"),
            'totals' => [
                'pages' => $allItems->count(),
                'cached'  => $allItems->count() - $missingItems->count(),
                'missing' => $missingItems->count(),
            ],
        ], 'Accepted', 202);
    }

    public function show(int $id, string $batchId): JsonResponse
    {
        Story::findOrfail($id);

        $batch = Bus::findBatch($batchId);

        if (!$batch) {
            return $this->sendError('Not Found', 'Batch not found.', 404);
        }

        $status = $this->resolveVisibleBatchStatus($batch);

        return $this->sendResponse([
            'status' => $status,
            'progress' => [
                'total' => $batch->totalJobs,
                'processed' => $batch->processedJobs(),
                'failed' => $batch->failedJobs,
                'percent' => $batch->totalJobs > 0
                    ? (int) round($batch->processedJobs() / $batch->totalJobs * 100)
                    : 0,
            ],
            'download_url' => $batch->finished()
                ? url("/stories/{$id}/items/export/mets")
                : null,
        ], $status, 200);
    }

    private function resolveFinalBatchStatus(Batch $batch): string
    {
        return match (true) {
            $batch->cancelled() => 'cancelled',
            $batch->hasFailures() => 'finished_with_failures',
            $batch->finished() => 'ready',
            default => 'failed',
        };
    }

    private function resolveVisibleBatchStatus(Batch $batch): string
    {
        return match (true) {
            $batch->cancelled() => 'cancelled',
            $batch->hasFailures() && $batch->finished() => 'finished_with_failures',
            $batch->finished() => 'ready',
            default => 'processing',
        };
    }
}
