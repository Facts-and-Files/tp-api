<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\CampaignStatsResource;
use App\Models\Campaign;
use App\Models\Score;
use App\Models\ScoreType;
use App\Traits\Rename;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CampaignStatsController extends ResponseController
{
    use Rename;

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

            $teams = $this->buildTeamStatistics($id);

            $grouped = [
                'CampaignId' => $id,
                'Summary'    => $summary,
                'Teams'      => $teams,
                'Users'      => $users
            ];

            $resource = new CampaignStatsResource($grouped);

            return $this->sendResponse($resource, 'CampaignStats fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    protected function buildTeamStatistics(int $campaignId): array
    {
        $campaign = Campaign::find($campaignId);

        $campaignItemIds = $campaign->stories->pluck('ItemIds')->flatten()->all();

        $scoreTypes = ScoreType::get();

        $allTeamsStats = [];

        foreach ($campaign->teams as $team) {
            $teamStats = [
                'TeamId' => $team['TeamId'],
                'Miles' => 0
            ];

            $teamScoreIds = DB::table('TeamScore')
                ->where('TeamId', $team['TeamId'])
                ->pluck('ScoreId')
                ->all();

            $teamCampaignScores = Score::whereIn('ScoreId', $teamScoreIds)
                ->whereIn('ItemId', $campaignItemIds)
                ->whereBetween('Timestamp', [$campaign->Start, $campaign->End])
                ->get();

            $scoreTypes->map(function ($score) use (&$teamStats) {
                $scoreName = $this->rename($score->Name);
                $teamStats[$scoreName] = 0;
            });

            $teamCampaignScores->map(function ($score) use ($scoreTypes, &$teamStats) {
                $nameAndRates = $scoreTypes->where('ScoreTypeId', $score->ScoreTypeId);
                $name = $this->rename($nameAndRates->pluck('Name')->first());
                $rate = $nameAndRates->pluck('Rate')->first();
                $teamStats[$name] += $score->Amount;
                $teamStats['Miles'] += $score->Amount * $rate;
            });


            $teamStats['Stories'] = $campaign
                ->stories
                ->filter(function ($story) use ($teamCampaignScores) {
                    $itemIds = array_merge(
                        $story->ItemIds->all(),
                        $teamCampaignScores->pluck('ItemId')->all()
                    );
                    return count(array_unique($itemIds)) > 0 ? true : false;
                })
                ->pluck('StoryId')
                ->unique()
                ->count();
            $teamStats['Items'] = $teamCampaignScores->pluck('ItemId')->unique()->count();
            $teamStats['Miles'] = ceil($teamStats['Miles']);

            $allTeamsStats[] = $teamStats;
        }

        return $allTeamsStats;
    }

    protected function buildCampaignStatisticsSummary(Collection $collection): array
    {
        $data = $this->buildStatistics($collection);

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
            'Miles'                => ceil($collection->pluck('Miles')->sum())
        ];
    }
}
