<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\HtrData;
use App\Http\Resources\HtrDataResource;

/**
 * @OA\Get(
 *     path="/api/htrdata",
 *     description="get all HTR data entries",
 *     @OA\Response(response="200", description="JSON response with all entries")
 * )
 */
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
        /* return $this->sendResponse(HtrDataResource::collection(HtrData::paginate(1000)), 'HtrData fetched.'); */
        /* return $this->sendResponse(HtrData::paginate(2), 'HtrData fetched.'); */
        /* return $this->sendResponse(HtrData::all(), 'HtrData fetched.'); */
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * Display the specified resource by item_id
     *
     * @param  int  $item_id
     * @return \Illuminate\Http\Response
     */
    public function showByItemId($item_id)
    {
        $htrData = HtrData::where('item_id', $item_id)->firstOrFail();
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
