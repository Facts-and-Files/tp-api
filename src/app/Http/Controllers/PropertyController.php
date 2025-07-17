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

        return $this->sendResponseWithMeta($collection, 'Properties fetched.');
    }

    public function show(int $id): JsonResponse
    {
        $data = Property::findOrFail($id);
        $resource = new PropertyResource($data);

        return $this->sendResponse($resource, 'Property fetched.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(Property::createRules());

        $property = Property::create($validated);

        return $this->sendResponse(new PropertyResource($property), 'Property inserted.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $property = Property::findOrFail($id);

        $validated = $request->validate(Property::updateRules());

        $property->update($validated);

        return $this->sendResponse(new PropertyResource($property), 'Property updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $property = Property::findOrfail($id);
        $resource = new PropertyResource($property->toArray());
        $property->delete();

        return $this->sendResponse($resource, 'Property deleted.');
    }
}
