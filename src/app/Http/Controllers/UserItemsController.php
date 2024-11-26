<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\UserItemsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserItemsController extends ResponseController
{
    public function index(Request $request, int $id): JsonResponse
    {
        try {
            $queries = $request->query();

            // set defaults
            $queries['limit'] = intval($queries['limit'] ?? 9);
            $queries['page'] = intval($queries['page'] ?? 1);
            $queries['threshold'] = intval($queries['threshold'] ?? 500);

            $data = DB::table('Score as s')
                ->join('Item as i', 's.ItemId', '=', 'i.ItemId')
                ->join('Story as st', 'i.StoryId', '=', 'st.StoryId')
                ->join('Project as p', 'st.ProjectId', '=', 'p.ProjectId')
                ->join('ScoreType as stype', 's.ScoreTypeId', '=', 'stype.ScoreTypeId')
                ->join('CompletionStatus as cs', 'i.CompletionStatusId', '=', 'cs.CompletionStatusId')
                ->select(
                    'p.Name as ProjectName',
                    'i.ItemId',
                    'i.Title AS ItemTitle',
                    'i.ImageLink AS ItemImageLink',
                    'cs.Name AS CompletionStatus',
                    DB::raw('MAX(s.Timestamp) AS LastEdit'),
                    'stype.Name AS ScoreType',
                    DB::raw('SUM(s.Amount) AS Amount')
                )
                ->where('s.UserId', $id)
                ->groupBy('p.Name', 'i.ItemId', 's.ScoreTypeId')
                ->orderBy('p.Name')
                ->orderBy('LastEdit', 'desc')
                ->limit($queries['threshold'])
                ->get();

            $grouped = $this->groupCollection($queries, $data);

            $collection = UserItemsResource::collection($grouped);

            return $this->sendResponseWithMeta($collection, 'Items fetched.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    private function groupCollection(array $queries, Collection $data): array
    {
        $limit = $queries['limit'];
        $page = $queries['page'];
        $offset = $limit * ($page - 1);
        $threshold = $queries['threshold'];

        $this->meta = [
            'limit' => $limit,
            'currentPage' => $page,
            'threshold' => $threshold,
        ];

        $groupedData = [];

        foreach ($data as $row) {
            $projectName = $row->ProjectName;
            $itemId = $row->ItemId;
            $itemTitle = $row->ItemTitle;
            $itemImageLink = $row->ItemImageLink;
            $itemLastEdit = $row->LastEdit;
            $completionStatus = $row->CompletionStatus;
            $scoreType = $row->ScoreType;

            // initialize project group if it doesn't exist
            if (!isset($groupedData[$projectName])) {
                $groupedData[$projectName] = [
                    'ProjectName' => $projectName,
                    'Items' => [],
                ];
            }

            // initialize item group within the project if it doesn't exist
            if (!isset($groupedData[$projectName]['Items'][$itemId])) {
                $groupedData[$projectName]['Items'][$itemId] = [
                    'ItemId' => (int) $itemId,
                    'ItemTitle' => $itemTitle ?? '',
                    'ItemImageLink' => $itemImageLink,
                    'CompletionStatus' => $completionStatus,
                    'LastEdit' => $itemLastEdit,
                    'Scores' => [],
                ];
            }

            // add score details to the item's scores
            $groupedData[$projectName]['Items'][$itemId]['Scores'][] = [
                'ScoreType' => $scoreType,
                'Amount' => (int) $row->Amount,
            ];
        }

        $groupedData = $this->limitAndOffsetItems($groupedData, $offset, $limit);

        return array_values($groupedData);
    }

    private function limitAndOffsetItems(array $data, int $offset, int $limit): array
    {
        foreach ($data as $key => $project) {
            if (isset($project['Items']) && is_array($project['Items'])) {
                $data[$key]['Items'] = array_slice($project['Items'], $offset, $limit);
            }
        }

        return $data;
    }
}
