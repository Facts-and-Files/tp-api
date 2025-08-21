<?php

namespace App\Http\Controllers;

use App\Events\PlaceInserted;
use App\Http\Controllers\ResponseController;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Models\Project;
use App\Models\Story;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'latMin' => 'numeric|between:-90,90',
            'latMax' => 'numeric|between:-90,90',
            'lngMin' => 'numeric|between:-180,180',
            'lngMax' => 'numeric|between:-180,180',
        ]);

        $queryColumns = [
            'Name'         => 'Place.Name',
            'WikidataName' => 'Place.WikidataName',
            'WikidataId'   => 'Place.WikidataId',
            'ItemId'       => 'Place.ItemId',
            'UserId'       => 'Place.UserId',
            'StoryId'      => 'Item.StoryId',
            'ProjectId'    => 'Story.ProjectId',
            'PlaceRole'    => 'Place.PlaceRole',
        ];

        $initialSortColumn = 'Place.PlaceId';

        $query = $this->buildQueryByParentId($request);

        $data = $this->getDataByRequest($request, $query, $queryColumns, $initialSortColumn);

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
        $request->merge(['ItemId' => $itemId]);

        return $this->index($request);
    }

    public function showByStoryId(Request $request, int $storyId): JsonResponse
    {
        $request->merge(['StoryId' => $storyId]);

        return $this->index($request);
    }

    public function showByProjectId(Request $request, int $projectId): JsonResponse
    {
        $request->merge(['ProjectId' => $projectId]);

        return $this->index($request);
    }

    private function buildQueryByParentId(Request $request): Builder
    {
        $query = Place::query()
            ->join('Item', 'Place.ItemId', '=', 'Item.ItemId')
            ->select('Place.*', 'Item.Title as ItemTitle');


        if ($request->has('ProjectId')) {
            $projectId = $request['ProjectId'];
            Project::findOrFail($projectId);
            $query->join('Story', 'Item.StoryId', '=', 'Story.StoryId')
                  ->where('Story.ProjectId', '=', $projectId);
        }

        if ($request->has('StoryId')) {
            $storyId = $request['StoryId'];
            Story::findOrFail($storyId);
            $query->where('Item.StoryId', '=', $storyId);
        }

        if ($request->has('ItemId')) {
            $itemId = $request['ItemId'];
            Item::findOrFail($itemId);
            $query->where('Place.ItemId', '=', $itemId);
        }

        return $query;
    }
}
