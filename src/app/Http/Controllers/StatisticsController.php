<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\StatisticsResource;
use App\Models\SummaryStatsView;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $queryColumns = [
                'Year'        => 'Year',
                'Month'       => 'Month',
                'ScoreTypeId' => 'ScoreTypeId'
            ];

            $request->merge(['limit' => 1000000]);

            $initialSortColumn = 'Year';

            $model = new SummaryStatsView();

            $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);


            $resource = new StatisticsResource($data);

            return $this->sendResponse($resource, 'Statistics fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function alltimeIndex(): JsonResponse
    {
        try {
            $items  = DB::table('Item')->select('CompletionStatusId');
            $scores = DB::table('Score')->select('ScoreTypeId', 'UserId', 'Amount');

            $data = [
                'ActiveUsers'          => $this->countDistinctUsers($scores),
                'ItemsNotStarted'      => $this->countByCompletionStatusId($items, 1),
                'ItemsEdited'          => $this->countByCompletionStatusId($items, 2),
                'ItemsReviewed'        => $this->countByCompletionStatusId($items, 3),
                'ItemsCompleted'       => $this->countByCompletionStatusId($items, 4),
                'ManualTranscriptions' => $this->sumByScoreTypeId($scores, 2),
                'HTRTranscriptions'    => $this->sumByScoreTypeId($scores, 5),
                'Locations'            => $this->sumByScoreTypeId($scores, 1),
                'Enrichments'          => $this->sumByScoreTypeId($scores, 3),
                'Descriptions'         => $this->sumByScoreTypeId($scores, 4)
            ];

            $resource = new StatisticsResource($data);

            return $this->sendResponse($resource, 'Statistics fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    private function sumByScoreTypeId(Builder $query, int $scoreTypeId): int
    {
        $cloned = clone $query;
        return intval($cloned->where('ScoreTypeId', '=', $scoreTypeId)->sum('Amount'));
    }

    private function countByCompletionStatusId(Builder $query, int $completionStatusId): int
    {
        $cloned = clone $query;
        return $cloned->where('CompletionStatusId', '=', $completionStatusId)->count();
    }

    private function countDistinctUsers(Builder $query): int
    {
        $cloned = clone $query;
        return $cloned->distinct()->count('UserId');
    }
}
