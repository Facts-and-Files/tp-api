<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Property;
use App\Http\Resources\PropertyResource;

class PropertyController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'Value' => 'Value',
            'PropertyTypeId' => 'PropertyTypeId',
        ];

        $initialSortColumn = 'PropertyId';

        $model = new Property();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = PropertyResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Projects fetched.');
    }
}
