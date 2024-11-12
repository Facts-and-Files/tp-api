<?php

namespace App\Http\Controllers;

use App\Models\UserStatsView;
use App\Http\Controllers\ResponseController;
use App\Http\Resources\UserStatsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserStatsController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'UserId',
            'Items',
            'Locations',
            'ManualTranscriptions',
            'Enrichments',
            'Descriptions',
            'HTRTranscriptions',
            'Miles',
        ];

        $initialSortColumn = 'Miles';

        $orderDir = $request->query()['orderDir'] ?? null;
        if (!$orderDir) {
            $request->merge(['orderDir' => 'desc']);
        }

        $model = new UserStatsView();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = UserStatsResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Users statistics fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = UserStatsView::findOrFail($id);

            $resource = new UserStatsResource($data);

            return $this->sendResponse($resource, 'UserStats fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }
}
