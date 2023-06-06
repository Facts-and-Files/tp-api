<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\AutoEnrichment;
use App\Http\Resources\AutoEnrichmentResource;

class AutoEnrichmentController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $data = $this->getDataByRequest($request);

        if(!$data) {
            return $this->sendError('Invalid data', $request, ' not valid', 400);
        }

        $collection = AutoEnrichmentResource::collection($data);

        return $this->sendResponse($collection, 'AutoEnrichments fetched.');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = new AutoEnrichment();
            $data->fill($request->all());
            $data->StoryId = $request['StoryId'];
            $data->ItemId = $request['ItemId'];
            $data->save();

            $resource = new AutoEnrichmentResource($data);

            return $this->sendResponse($resource, 'Auto Enrichment inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function storeBulk(Request $request): JsonResponse
    {
        $data = $request->all();

        $inserted = [];
        $errors = [];

        foreach ($data as $item) {
            $autoEnrichment = new AutoEnrichment();
            $autoEnrichment->fill($item);

            $autoEnrichment->StoryId = $item['StoryId'] ?? null;
            $autoEnrichment->ItemId = $item['ItemId'] ?? null;

            try {
                $autoEnrichment->save();
                $inserted[] = $autoEnrichment;
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        $instertedResource = new AutoEnrichmentResource($inserted);
        $errorResource = new AutoEnrichmentResource($errors);

        $insertedCount = count($inserted);
        $errorsCount = count($errors);

        if ($insertedCount === 0 && $errorsCount > 0) {
            return $this->sendError('Invalid data', $errors, 400);
        }

        if ($insertedCount > 0 && $errorsCount === 0) {
            return $this->sendResponse($instertedResource, 'All Auto Enrichments inserted.');
        }

        if ($insertedCount > 0 && $errorsCount > 0) {
            return $this->sendPartlyResponse($instertedResource, $errorResource, 'Some Auto Enrichments inserted.');
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = AutoEnrichment::findOrFail($id);
            $resource = new AutoEnrichmentResource($data);

            return $this->sendResponse($resource, 'Auto Enrichment fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception);
        }
    }

    public function showByItemId(int $itemId, Request $request): JsonResponse
    {
        try {
            $queries = $request->query();
            $data = AutoEnrichment::where('ItemId', $itemId);
            $data = $this->filterDataByQueries($data, $queries);
            $resource = new AutoEnrichmentResource($data);

            return $this->sendResponse($resource, 'Auto Enrichment fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function showByStoryId(int $storyId, Request $request): JsonResponse
    {
        try {
            $queries = $request->query();
            $data = AutoEnrichment::where('StoryId', $storyId);
            $data = $this->filterDataByQueries($data, $queries);
            $resource = new AutoEnrichmentResource($data);

            return $this->sendResponse($resource, 'Auto Enrichment fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $autoEnrichmentData = AutoEnrichment::findOrFail($id);
            $autoEnrichmentData->fill($request->all());
            $autoEnrichmentData->save();

            return $this->sendResponse(new AutoEnrichmentResource($autoEnrichmentData), 'Auto Enrichment updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $autoEnrichmentData = AutoEnrichment::findOrFail($id);
            $resource = $autoEnrichmentData->toArray();
            $resource = new AutoEnrichmentResource($resource);
            $autoEnrichmentData->delete();

            return $this->sendResponse($resource, 'Auto Enrichment deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    protected function getDataByRequest(Request $request): Collection
    {
        $queries = $request->query();

        $queryColumns = [
            'Name' => 'Name',
            'Type' => 'Type',
            'StoryId' => 'StoryId',
            'ItemId' => 'ItemId'
        ];

        $autoEnrichmentData = new AutoEnrichment();

        $data = $autoEnrichmentData->whereRaw('1 = 1');

        foreach ($queries as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queryColumns)) {
                $data->where($queryColumns[$queryName], $queryValue);
            }
        }

        $data = $this->filterDataByQueries($data, $queries);

        return $data;
    }

    protected function filterDataByQueries(Builder $data, array $queries): Collection
    {
        $limit = $queries['limit'] ?? 100;
        $page = $queries['page'] ?? 1;
        $orderBy = $queries['orderBy'] ?? 'AutoEnrichmentId';
        $orderDir = $queries['orderDir'] ?? 'asc';
        $offset = $limit * ($page - 1);

        $filtered = $data
            ->limit($limit)
            ->offset($offset)
            ->orderBy($orderBy, $orderDir)
            ->get();

        return $filtered;
    }
}
