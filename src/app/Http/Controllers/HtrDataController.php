<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\HtrData;
use App\Http\Resources\HtrDataResource;
use Illuminate\Http\Request;

class HtrDataController extends ResponseController
{
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
            return $this->sendError('Invalid data', $exception->getMessage());
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
        $htrData = HtrData::findOrfail($id);
        return $this->sendResponse(new HtrDataResource($htrData), 'HtrData fetched.');
    }

    /**
     * Display the specified resource by process_id
     *
     * @param  int  $process_id
     * @return \Illuminate\Http\Response
     */
    public function showByProcessId($process_id)
    {
        $htrData = HtrData::where('process_id', $process_id)->firstOrFail();
        return $this->sendResponse(new HtrDataResource($htrData), 'HtrData fetched.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HtrData  $htrData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HtrData $htrData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HtrData  $htrData
     * @return \Illuminate\Http\Response
     */
    public function destroy(HtrData $htrData)
    {
        //
    }

}
