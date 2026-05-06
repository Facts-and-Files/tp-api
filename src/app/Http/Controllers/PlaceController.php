<?php

namespace App\Http\Controllers;

use App\Events\PlaceInserted;
use App\Http\Resources\PlaceResource;
use App\Models\Dataset;
use App\Models\Item;
use App\Models\Place;
use App\Models\Project;
use App\Models\Story;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'Name' => 'Place.Name',
            'WikidataName' => 'Place.WikidataName',
            'WikidataId' => 'Place.WikidataId',
            'ItemId' => 'Place.ItemId',
            'UserId' => 'Place.UserId',
            'StoryId' => 'Item.StoryId',
            'ProjectId' => 'Story.ProjectId',
            'DatasetId' => 'Story.DatasetId',
            'PlaceRole' => 'Place.PlaceRole',
        ];

        $initialSortColumn = 'Place.PlaceId';

        $query = $this->buildQueryByParentId($request)->with('links');
        ;

        $data = $this->getDataByRequest($request, $query, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = PlaceResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Places fetched.');
    }

    public function show(int $id): JsonResponse
    {
        $place = Place::with('links')->findOrFail($id);
        $resource = new PlaceResource($place);

        return $this->sendResponse($resource, 'Place fetched.');
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $this->validatePlaceRequest($request, true);

        $place = DB::transaction(function () use ($request, $validatedData) {
            $place = new Place();
            $place->fill($request->except('Links'));
            $place->save();

            $this->syncLinks($place, $validatedData['Links'] ?? []);

            return $place->load('links');
        });

        PlaceInserted::dispatch($place->ItemId);

        return $this->sendResponse(new PlaceResource($place), 'Place inserted.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validatedData = $this->validatePlaceRequest($request, false);

        $place = DB::transaction(function () use ($request, $validatedData, $id) {
            $place = Place::findOrFail($id);
            $place->fill($request->except('Links'));
            $place->save();

            if ($request->has('Links')) {
                $this->syncLinks($place, $validatedData['Links'] ?? []);
            }

            return $place->load('links');
        });

        return $this->sendResponse(new PlaceResource($place), 'Place updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $place = Place::with('links')->findOrFail($id);
        $resource = new PlaceResource($place);
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

    public function showByDatasetId(Request $request, int $datasetId): JsonResponse
    {
        $request->merge(['DatasetId' => $datasetId]);

        return $this->index($request);
    }

    private function validatePlaceRequest(Request $request, bool $isCreate): array
    {
        $rules = [
            'Links' => 'sometimes|array',
            'Links.*.Provider' => 'required_with:Links|string|max:255',
            'Links.*.Url' => 'required_with:Links|url|max:1000',
        ];

        if ($isCreate) {
            $rules['ItemId'] = 'required';
            $rules['Longitude'] = 'required';
            $rules['Latitude'] = 'required';
        }

        return $request->validate($rules);
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

        if ($request->has('DatasetId')) {
            $datasetId = $request['DatasetId'];
            Dataset::findOrFail($datasetId);

            $query->join('Story', 'Item.StoryId', '=', 'Story.StoryId')
                  ->where('Story.DatasetId', '=', $datasetId);
        }

        if ($request->has('LinkProvider')) {
            $provider = $request->get('LinkProvider');

            $query->whereExists(function ($sub) use ($provider) {
                $sub->from('PlaceLink')
                    ->whereColumn('PlaceLink.PlaceId', 'Place.PlaceId')
                    ->where('PlaceLink.Provider', '=', $provider);
            });
        }

        return $query;
    }

    private function syncLinks(Place $place, array $links = []): void
    {
        $place->links()->delete();

        foreach ($links as $link) {
            $place->links()->create([
                'Provider' => $link['Provider'],
                'Url' => $link['Url'],
            ]);
        }
    }

}
