<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\ItemStats;
use App\Http\Resources\ItemStatsResource;

class ItemStatsController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'StoryId' => 'StoryId'
        ];

        $initialSortColumn = 'LastUpdated';

        $model = new ItemStats();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = ItemStatsResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'ItemStats fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = ItemStats::findOrFail($id);
            $resource = new ItemStatsResource($data);

            return $this->sendResponse($resource, 'ItemStats fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }
}
