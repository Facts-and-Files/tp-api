<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\AutoEnrichment;
use App\Http\Resources\AutoEnrichmentResource;
use Illuminate\Http\Request;

class AutoEnrichmentController extends ResponseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $this->getDataByRequest($request);

        if(!$data) {
            return $this->sendError('Invalid data', $request, ' not valid', 400);
        }

        $collection = AutoEnrichmentResource::collection($data);

        return $this->sendResponse($collection, 'AutoEnrichments fetched.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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

    /**
     * Store a newly created resource (bulk) in storage,
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBulk(Request $request)
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

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = AutoEnrichment::findOrFail($id);
            $resource = new AutoEnrichmentResource($data);

            return $this->sendResponse($resource, 'Auto Enrichment fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int                       $itemId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showByItemId($itemId, Request $request)
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

    /**
     * Display the specified resource.
     *
     * @param  int                       $itemId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showByStoryId($storyId, Request $request)
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $autoEnrichmentData = AutoEnrichment::findOrFail($id);
            $autoEnrichmentData->delete();

            return $this->sendResponse(new AutoEnrichmentResource($autoEnrichmentData), 'Auto Enrichment deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    /**
     * Get data defined by request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array \Illuminate\Database\Eloquent\Collection $data
     */
    protected function getDataByRequest(Request $request)
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

    /**
     * Filter data by requested queries
     *
     * @param  \Illuminate\Http\Resources $data
     * @param  array                      $queries
     * @return array \Illuminate\Database\Eloquent\Collection $data
     */
    protected function filterDataByQueries($data, $queries)
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
