<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\Item;
use App\Http\Resources\ItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'StoryId' => 'StoryId',
            'ItemId'  => 'ItemId'
        ];

        $initialSortColumn = 'ItemId';

        $model = new Item();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        $data = $this->filterDataByFieldlist($data, $request, ['StoryId', 'ItemId'], $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = ItemResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Items fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Item::findOrFail($id);
            $resource = new ItemResource($data);

            return $this->sendResponse($resource, 'Item fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = Item::findOrfail($id);

            // TranscriptionStatus to review when changing from manual to htr
            if ($item->TranscriptionSource === 'manual' && $request['TranscriptionSource'] === 'htr') {
                $item->TranscriptionStatusId = 3;
            }

            $item->fill($request->all());
            $item->save();

            return $this->sendResponse(new ItemResource($item), 'Item updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
