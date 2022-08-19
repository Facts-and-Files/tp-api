<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\HtrData;
use App\Http\Resources\HtrDataResource;
use Illuminate\Http\Request;

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
     * @param  int  $itemId
     * @return \Illuminate\Http\Response
     */
    public function showByItemId($itemId)
    {
        try {
            $data = HtrData::where('item_id', $itemId)->get();
            $resource = new HtrDataResource($data);

            return $this->sendResponse($resource, 'HtrData fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function showByUserId($userId)
    {
        try {
            $data = HtrData::where('user_id', $userId)->get();
            $resource = new HtrDataResource($data);

            return $this->sendResponse($resource, 'HtrData fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     *
     * Display the specified resource.
     *
     * @param  int  $processId
     * @return \Illuminate\Http\Response
     */
    public function showByProcessId($processId)
    {
        try {
            $data = HtrData::where('process_id', $processId);
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
     * @param  int  $item_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $item_id)
    {
        /* try { */
        /*     $htrData = HtrData::findOrfail($item_id); */
        /*     $htrData->fill($request->all()); */
        /*     $htrData->save(); */

        /*     return $this->sendResponse(new HtrDataResource($htrData), 'HtrData updated.'); */
        /* } catch(\Exception $exception) { */
        /*     return $this->sendError('Invalid data', $exception->getMessage(), 400); */
        /* } */
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $item_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($item_id)
    {
        /* try { */
        /*     $htrData = HtrData::findOrfail($item_id); */
        /*     $htrData->delete(); */

        /*     return $this->sendResponse(new HtrDataResource($htrData), 'HtrData deleted.'); */
        /* } catch(\Exception $exception) { */
        /*     return $this->sendError('Invalid data', $exception->getMessage(), 400); */
        /* } */
    }

    /**
     * Get data defined by request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed \Illuminate\Http/Models $data
     */
    protected function getDataByRequest(Request $request) {

        $limit = $request->query('limit') ?? 100;

        $queries = array(
            'processId' => 'process_id',
            'userId'    => 'user_id',
            'itemId'    => 'item_id',
        );

        foreach ($request->query() as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queries)) {
                return $data = HtrData::where($queries[$queryName], $queryValue)->paginate($limit);
            }
            return null;
        }

        return $data = HtrData::paginate($limit);
    }

}
