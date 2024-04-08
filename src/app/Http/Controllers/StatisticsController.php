<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\StatisticsResource;
use App\Models\SummaryStatsView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Collection;
// use Illuminate\Support\Facades\DB;

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

            $initialSortColumn = 'Year';

            $model = new SummaryStatsView();

            $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);


            $resource = new StatisticsResource($data);

            return $this->sendResponse($resource, 'Statistics fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
