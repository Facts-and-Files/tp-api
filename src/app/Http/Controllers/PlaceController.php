<?php

namespace App\Http\Controllers;

use App\Events\PlaceInserted;
use App\Http\Controllers\ResponseController;
use App\Http\Resources\PlaceResource;
use App\Models\Item;
use App\Models\Story;
use App\Models\Project;
use App\Models\Place;
use App\Models\PlaceCombinedDetails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'Name'         => 'Name',
            'WikidataName' => 'WikidataName',
            'WikidataId'   => 'WikidataId',
            'ItemId'       => 'ItemId',
            'UserId'       => 'UserId',
            'StoryId'      => 'StoryId',
            'ProjectId'    => 'ProjectId',
            'PlaceRole'    => 'PlaceRole',
        ];

        $initialSortColumn = 'PlaceId';

        $model = new PlaceCombinedDetails();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = PlaceResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Places fetched.');
    }

    public function show(int $id): JsonResponse
    {
        $place = Place::findOrFail($id);
        $resource = new PlaceResource($place);

        return $this->sendResponse($resource, 'Place fetched.');
    }

    public function store(Request $request): JsonResponse
    {
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

        PlaceInserted::dispatch($place->ItemId);

        $resource = new PlaceResource($place);

        return $this->sendResponse($resource, 'Place inserted.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $place = Place::findOrfail($id);
        $place->fill($request->all());
        $place->save();

        $resource = new PlaceResource($place);

        return $this->sendResponse($resource, 'Place updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $place = Place::findOrfail($id);
        $resource = new PlaceResource($place->toArray());
        $place->delete();

        return $this->sendResponse($resource, 'Place deleted.');
    }

    public function showByItemId(Request $request, int $itemId): JsonResponse
    {
        Item::findOrFail($itemId);

        $query = Place::where('ItemId', $itemId);
        $places = $this->filterDataByQueries($query, request()->all(), 'PlaceId');

        return $this->sendResponseWithMeta(
            PlaceResource::collection($places),
            'Places fetched.'
        );
    }

    public function showByStoryId(Request $request, int $storyId): JsonResponse
    {
        Story::findOrFail($storyId);

        $query = Place::whereHas('item', function ($q) use ($storyId) {
            $q->where('StoryId', $storyId);
        });

        $places = $this->filterDataByQueries($query, request()->all(), 'PlaceId');

        return $this->sendResponseWithMeta(
            PlaceResource::collection($places),
            'Places fetched.'
        );
    }

    public function showByProjectId(Request $request, int $projectId): JsonResponse
    {
        Project::findOrFail($projectId);

        $query = Place::whereHas('item.story', function ($q) use ($projectId) {
            $q->where('ProjectId', $projectId);
        });

        $places = $this->filterDataByQueries($query, request()->all(), 'PlaceId');

        return $this->sendResponseWithMeta(PlaceResource::collection($places), 'Places fetched.');
    }
}
