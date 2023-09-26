<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\Item;
use App\Http\Resources\ItemResource;
use Illuminate\Http\Request;

class ItemController extends ResponseController
{
    /**
     * Display a paginated listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $queryColumns = [
            'StoryId' => 'StoryId'
        ];

        $initialSortColumn = 'ItemId';

        $model = new Item();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = ItemResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Items fetched.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = Item::findOrFail($id);
            $resource = new ItemResource($data);

            return $this->sendResponse($resource, 'Item fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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
