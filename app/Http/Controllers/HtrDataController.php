<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use App\Models\HtrData as HtrData;
use App\Http\Resources\HtrDataResource as HtrDataResource;

class HtrDataController extends BaseController
{
    public function index()
    {
        $allHtrData = HtrData::all();
        return $this->sendResponse(HtrDataResource::collection($allHtrData), 'HtrData fetched.');
    }

}
