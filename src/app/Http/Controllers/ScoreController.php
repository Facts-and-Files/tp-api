<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Score;
use App\Http\Resources\ScoreResource;

class ScoreController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'ItemId'      => 'ItemId',
            'UserId'      => 'UserId',
            'ScoreTypeId' => 'ScoreTypeId'
        ];

        $initialSortColumn = 'Timestamp';

        $model = new Score();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = ScoreResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Scores fetched.');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $score = new Score();

            if (!isset($request['Amount']) || $request['Amount'] <= 0) {
                return $this->sendResponse(new ScoreResource($score), 'Nothing to insert.');
            }

            $score->fill($request->all());
            $score->save();

            return $this->sendResponse(new ScoreResource($score), 'Score inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
