<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\HtrData;
use App\Http\Resources\HtrDataResource;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Array_;

class HtrDataController extends ResponseController
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

        $collection = HtrDataResource::collection($data);

        return $this->sendResponse($collection, 'HtrData fetched.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $data = new HtrData();
            $data->fill($request->all());
            $data->item_id = $request->item_id;
            $data->process_id = $request->process_id;
            $data->user_id = $request->user_id;
            $data->save();

            $resource = new HtrDataResource($data);

            return $this->sendResponse($resource, 'HtrData inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
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
            $data = HtrData::findOrFail($id);
            $resource = new HtrDataResource($data);

            return $this->sendResponse($resource, 'HtrData fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int                       $userId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showByItemId($itemId, Request $request)
    {
        try {
            $data = HtrData::where('item_id', $itemId);
            $data = $this->filterDataByRequest($data, $request);
            $resource = new HtrDataResource($data);

            return $this->sendResponse($resource, 'HtrData fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int                       $userId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showByUserId($userId, Request $request)
    {
        try {
            $data = HtrData::where('user_id', $userId);
            $data = $this->filterDataByRequest($data, $request);
            $resource = new HtrDataResource($data);

            return $this->sendResponse($resource, 'HtrData fetched.');
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
            $htrData = HtrData::findOrfail($id);
            $htrData->fill($request->all());
            $htrData->save();

            return $this->sendResponse(new HtrDataResource($htrData), 'HtrData updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $htrData = HtrData::findOrfail($id);
            $htrData->delete();

            return $this->sendResponse(new HtrDataResource($htrData), 'HtrData deleted.');
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

        $queryColumns = array(
            'processId' => 'process_id',
            'userId'    => 'user_id',
            'itemId'    => 'item_id',
            'htrId'     => 'htr_id'
        );

        foreach ($queries as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queryColumns)) {
                $data = HtrData::where($queryColumns[$queryName], $queryValue);
                $data = $this->filterDataByQueries($data, $queries);

                return $data;
            }
        }

        $data = HtrData::whereRaw('1 = 1');
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
        $limit = $queries['limit'] ?? 1000;
        $page = $queries['page'] ?? 1;
        $orderBy = $queries['orderBy'] ?? 'id';
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
