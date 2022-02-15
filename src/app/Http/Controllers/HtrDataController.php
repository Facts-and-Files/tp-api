<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\HtrData;
use App\Http\Resources\HtrDataResource;
use Illuminate\Http\Request;

class HtrDataController extends ResponseController
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allHtrData = HtrData::all();
        return $this->sendResponse(HtrDataResource::collection($allHtrData), 'HtrData fetched.');
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
            $htrData = new HtrData();
            $htrData->fill($request->all());
            $htrData->item_id = $request->item_id;
            $htrData->process_id = $request->process_id;
            $htrData->save();

            return $this->sendResponse(new HtrDataResource($htrData), 'HtrData inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $item_id
     * @return \Illuminate\Http\Response
     */
    public function show($item_id)
    {
        try {
            $htrData = HtrData::findOrfail($item_id);

            return $this->sendResponse(new HtrDataResource($htrData), 'HtrData fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource by process_id
     *
     * @param  int  $process_id
     * @return \Illuminate\Http\Response
     */
    public function showByProcessId($process_id)
    {
        try {
            $htrData = HtrData::where('process_id', $process_id)->firstOrFail();

            return $this->sendResponse(new HtrDataResource($htrData), 'HtrData fetched.');
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
        try {
            $htrData = HtrData::findOrfail($item_id);
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
     * @param  int  $item_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($item_id)
    {
        try {
            $htrData = HtrData::findOrfail($item_id);
            $htrData->delete();

            return $this->sendResponse(new HtrDataResource($htrData), 'HtrData deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

}
