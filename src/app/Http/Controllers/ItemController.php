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
        $data = $this->getDataByRequest($request);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = ItemResource::collection($data);

        return $this->sendResponse($collection, 'Items fetched.');
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

    /**
     * Get data defined by request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array \Illuminate\Database\Eloquent\Collection $data
     */
    protected function getDataByRequest(Request $request)
    {
        $queries = $request->query();

        $queryColumns = [
            'StoryId' => 'StoryId'
        ];

        $item = new Item();

        $data = $item->whereRaw('1 = 1');

        foreach ($queries as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queryColumns)) {
                $data->where($queryColumns[$queryName], $queryValue);
            }
        }

        $data = $this->filterDataByQueries($data, $queries);

        return $data;
    }

    /**
     * Filter data by requested queries
     *
     * @param  \Illuminate\Http\Resources $data
     * @param  array                      $queries
     * @return array \Illuminate\Database\Eloquent\Collection $data
     */
    protected function filterDataByQueries($data, $queries)
    {
        $limit = $queries['limit'] ?? 100;
        $page = $queries['page'] ?? 1;
        $orderBy = $queries['orderBy'] ?? 'ItemId';
        $orderBy = $orderBy === 'id' ? 'ItemId' : $orderBy;
        $orderDir = $queries['orderDir'] ?? 'asc';
        $offset = $limit * ($page - 1);

        $filtered = $data
            ->limit($limit)
            ->offset($offset)
            ->orderBy($orderBy, $orderDir)
            ->get();

        return $filtered;
    }
}
