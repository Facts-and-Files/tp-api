<?php


namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Jobs\PrepareItemAltoJob;
use App\Models\Item;
use App\Models\Story;
use App\Services\Export\MetsStoryReadiness;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;

class StoryMetsPreparationController extends ResponseController
{
    public function __construct(
        private readonly MetsStoryReadiness $metsStoryReadiness,
    ) {}

    public function store(int $id): JsonResponse
    {
        $story = Story::findOrFail($id);

        if ($this->metsStoryReadiness->isReady($story)) {
            return $this->sendResponse([
                'status' => 'ready',
                'download_url' => url("/stories/{$id}/items/export/mets"),
            ], 'METS XML is ready', 200);
        }

        $allItems = $this->metsStoryReadiness->allItems($story);
        $missingItems = $this->metsStoryReadiness->missingItems($story);

        $jobs = $missingItems
            ->map(fn (Item $item) => new PrepareItemAltoJob($item))
            ->all();

        $batch = Bus::batch($jobs)
            ->name("Prepare ALTO for METS story {$id}")
            ->allowFailures()
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

        $status = match (true) {
            $batch->finished() => 'ready',
            $batch->cancelled() => 'cancelled',
            $batch->hasFailures() && $batch->finished() => 'failed',
            default => 'processing',
        };

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
}
