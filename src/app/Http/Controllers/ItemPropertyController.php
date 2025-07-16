<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\Item;
use App\Models\Property;
use App\Http\Resources\ItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemPropertyController extends ResponseController
{
    public function attach(Request $request, int $itemId): JsonResponse
    {
        $item = Item::findOrFail($itemId);
        $property = Property::findOrfail($request->PropertyId);

        if ($item->properties()->where('Property.PropertyId', $request->PropertyId)->exists()) {
                throw ValidationException::withMessages(['Property already attached.']);
        }

        $item->properties()->attach($request->PropertyId);

        return $this->sendResponse(new ItemResource($item), 'Property attached to Item.');
    }

    public function detach(int $itemId, int $propertyId): JsonResponse
    {
        $item = Item::findOrFail($itemId);
        $property = Property::findOrfail($propertyId);

        if (!$item->properties()->where('Property.PropertyId', $propertyId)->exists()) {
                throw ValidationException::withMessages(['Property already detached.']);
        }

        $item->properties()->detach($propertyId);

        return $this->sendResponse(new ItemResource($item), 'Property detached from Item.');
    }
}
