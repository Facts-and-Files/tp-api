<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\PlaceResource;
use App\Models\Story;
use App\Models\Place;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlaceController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'Name'         => 'Name',
            'WikiDataName' => 'WikiDataName',
            'WikiDataId'   => 'WikiDataId',
            'ItemId'       => 'ItemId',
            'UserId'       => 'UserId',
            'PlaceRole'    => 'PlaceRole'
        ];

        $initialSortColumn = 'PlaceId';

        $model = new Place();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = PlaceResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Places fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Place::findOrFail($id);
            $resource = new PlaceResource($data);

            return $this->sendResponse($resource, 'Place fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'ItemId'    => 'required',
                'Longitude' => 'required',
                'Latitude'  => 'required'
            ]);

            $place = new Place();
            $place->ItemId = $validatedData['ItemId'];
            $place->Latitude = $validatedData['Latitude'];
            $place->Longitude = $validatedData['Longitude'];
            $place->fill($request->all());
            $place->save();

            // PlaceInserted::dispatch($request['ItemId']);

            $resource = new PlaceResource($place);

            return $this->sendResponse($resource, 'Place inserted.');
        } catch (ValidationException $exception) {
            return $this->sendError('Validation error', $exception->errors(), 422);
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $place = Place::findOrfail($id);
            $place->fill($request->all());
            $place->save();

            $resource = new PlaceResource($place);

            return $this->sendResponse($resource, 'Place updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $place = Place::findOrfail($id);
            $resource = new PlaceResource($place->toArray());
            $place->delete();

            return $this->sendResponse($resource, 'Place deleted.');
        } catch (ModelNotFoundException $exception) {
            return $this->sendError('Not found', $exception->getMessage(), 404);
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function showByItemId(int $itemId): JsonResponse
    {
        try {
            $data = Place::where('ItemId', $itemId)->get();
            $resource = new PlaceResource($data);

            return $this->sendResponse($resource, 'Places fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function showByStoryId(int $storyId): JsonResponse
    {
        try {
            $story = Story::with('items.places')->find($storyId);
            $data = $story->items->pluck('places')->unique();
            $resource = new PlaceResource($data);

            return $this->sendResponse($resource, 'Places fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }
}
