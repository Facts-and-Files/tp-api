<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Http\Resources\ImportResource;

class ImportController extends ResponseController
{
    public function store(Request $request): JsonResponse
    {
        try {
        //     $data = new Project();
        //     $data->fill($request->all());
        //     $data->save();
        //
            return $this->sendResponse(new ImportResource([]), 'Import successfull.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
