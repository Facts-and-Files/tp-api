<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\HtrData;
use App\Http\Resources\HtrDataResource;

/**
 * @OA\Get(
 *     path="/api/htrdata",
 *     description="get all HTR data entries",
 *     @OA\Response(response="", description="JSON response with all entries")
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
     * @param  \App\Models\HtrData  $htrData
     * @return \Illuminate\Http\Response
     */
    public function show(HtrData $htrData)
    {
        //
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
