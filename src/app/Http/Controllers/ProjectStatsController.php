<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectStatsResource;
use App\Models\Project;
use App\Models\ProjectStatsView;
use App\Models\Team;
use App\Traits\Rename;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectStatsController extends ResponseController
{
    use Rename;

    public function show(int $id): JsonResponse
    {
        try {
            $data = DB::select('SELECT * FROM project_stats_view WHERE ProjectId = ?', [$id]);


            if (empty($data)) {
                return $this->sendError('Not found', 'No statistics exists for this Project yet.');
            }

            $collection = collect($data);

            $summary = $this->buildProjectStatisticsSummary($collection);

            $users = [];
            $collection->groupBy('UserId')->each(function ($item, $userId) use (&$users) {
                $user = $this->buildStatistics($item);
                $user['UserId'] = $userId;
                $users[] = $user;
            });

            $teams = $this->buildTeamStatisticsByProject($id);

            $grouped = [
                'ProjectId' => $id,
                'Summary' => $summary,
                'Teams' => $teams,
                'Users' => $users,
            ];

            $resource = new ProjectStatsResource($grouped);

            return $this->sendResponse($resource, 'Projects statistics fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    protected function buildTeamStatisticsByProject(int $projectId): array
    {
        $project = Project::findOrFail($projectId);

        $teams = Team::query()
            ->whereExists(function ($query) use ($projectId) {
                $query->select(DB::raw(1))
                    ->from('TeamScore as ts')
                    ->join('Score as s', 's.ScoreId', '=', 'ts.ScoreId')
                    ->join('Item as i', 'i.ItemId', '=', 's.ItemId')
                    ->join('Story as st', 'st.StoryId', '=', 'i.StoryId')
                    ->whereColumn('ts.TeamId', 'Team.TeamId')
                    ->where('st.ProjectId', $projectId);
            })
            ->get();

        $allTeamsStats = [];

        foreach ($teams as $team) {
            $teamItemIds = DB::table('TeamScore as ts')
                ->join('Score as s', 's.ScoreId', '=', 'ts.ScoreId')
                ->join('Item as i', 'i.ItemId', '=', 's.ItemId')
                ->join('Story as st', 'st.StoryId', '=', 'i.StoryId')
                ->where('ts.TeamId', $team->TeamId)
                ->where('st.ProjectId', $projectId)
                ->pluck('s.ItemId')
                ->unique();

            $row = ProjectStatsView::query()
                ->where('ProjectId', $projectId)
                ->whereIn('ItemId', $teamItemIds)
                ->selectRaw('
                    SUM(Locations) AS Locations,
                    SUM(ManualTranscriptions) AS ManualTranscriptions,
                    SUM(Enrichments) AS Enrichments,
                    SUM(Descriptions) AS Descriptions,
                    SUM(HTRTranscriptions) AS HTRTranscriptions,
                    SUM(Miles) AS Miles,
                    COUNT(DISTINCT StoryId) AS Stories,
                    COUNT(DISTINCT ItemId) AS Items
                ')
                ->first();

            $allTeamsStats[] = [
                'TeamId' => $team->TeamId,
                'Locations' => (int) ($row->Locations ?? 0),
                'ManualTranscriptions' => (int) ($row->ManualTranscriptions ?? 0),
                'Enrichments' => (int) ($row->Enrichments ?? 0),
                'Descriptions' => (int) ($row->Descriptions ?? 0),
                'HTRTranscriptions' => (int) ($row->HTRTranscriptions ?? 0),
                'Stories' => (int) ($row->Stories ?? 0),
                'Items' => (int) ($row->Items ?? 0),
                'Miles' => (int) ceil($row->Miles ?? 0),
            ];
        }

        return $allTeamsStats;
    }

    protected function emptyTeamStats(int $teamId): array
    {
        return [
            'TeamId'              => $teamId,
            'Locations'           => 0,
            'ManualTranscriptions' => 0,
            'Enrichments'         => 0,
            'Descriptions'        => 0,
            'HTRTranscriptions'   => 0,
            'Stories'             => 0,
            'Items'               => 0,
            'Miles'               => 0,
        ];
    }

    protected function buildProjectStatisticsSummary(Collection $collection): array
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
            'Miles'                => ceil($collection->pluck('Miles')->sum()),
        ];
    }
}
