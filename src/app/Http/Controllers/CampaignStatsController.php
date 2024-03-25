<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\CampaignStatsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CampaignStatsController extends ResponseController
{
    public function show(int $id): JsonResponse
    {
        try {
            $data = DB::select('SELECT * FROM campaign_stats_view WHERE CampaignId = ?', [$id]);


            if (empty($data)) {
                return $this->sendError('Not found', 'No statistics exists for this CampaignId.');
            }

            $collection = collect($data);

            $summary = $this->buildCampaignStatisticsSummary($collection);

            $users = [];
            $collection->groupBy('UserId')->each(function ($item, $userId) use (&$users) {
                $user = $this->buildStatistics($item);
                $user['UserId'] = $userId;
                $users[] = $user;
            });

            $grouped = [
                'CampaignId' => $id,
                'Summary'    => $summary,
                'Users'      => $users
            ];

            $resource = new CampaignStatsResource($grouped);

            return $this->sendResponse($resource, 'CampaignStats fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    protected function buildCampaignStatisticsSummary(Collection $collection): array
    {
        $data = $this->buildStatistics($collection);
        $data['Users'] = $collection->pluck('UserId')->unique()->count();

        return $data;
    }

    protected function buildStatistics(Collection $collection): array
    {
        return [
            'Stories'              => $collection->pluck('StoryId')->unique()->count(),
            'Items'                => $collection->pluck('ItemId')->unique()->count(),
            'Locations'            => $collection->pluck('Locations')->sum(),
            'ManualTranscriptions' => $collection->pluck('ManualTranscriptions')->sum(),
            'Enrichments'          => $collection->pluck('Enrichments')->sum(),
            'Descriptions'         => $collection->pluck('Descriptions')->sum(),
            'HTRTranscriptions'    => $collection->pluck('HTRTranscriptions')->sum(),
            'Miles'                => $collection->pluck('Miles')->sum()
        ];
    }
}
