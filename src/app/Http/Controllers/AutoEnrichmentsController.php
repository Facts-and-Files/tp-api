<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\AutoEnrichments;
use App\Http\Resources\AutoEnrichmentsResource;
use Illuminate\Http\Request;

class AutoEnrichmentsController extends ResponseController
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

        $collection = AutoEnrichmentsResource::collection($data);

        return $this->sendResponse($collection, 'Auto Enrichments fetched.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            $data = new AutoEnrichmentsData();
            $data->fill($request->all());
            $data->Name = $request->Name;
            $data->Type = $request->Type;
            $data->WikiData = $request->WikiData;
            $data->StoryId = $request->StoryId;
            $data->ItemId = $request->ItemId;
            $data->ExternalId = $request->ExternalId;
            $data->save();

            $resource = new AutoEnrichmentsResource($data);

            return $this->sendResponse($resource, 'Auto Enrichment inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
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
            $data = AutoEnrichmentsData::findOrFail($id);
            $resource = new AutoEnrichmentsResource($data);

            return $this->sendResponse($resource, 'Auto Enrichments fetched.');
        } catch (\Eception $exception) {
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
            $data = AutoEnrichmentsData::where('ItemId', $itemId);
            $data = $this->filterDataByQueries($data, $queries);
            $resource = new AutoEnrichmentsResource($data);

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
            $data = AutoEnrichmentsData::where('StoryId', $storyId);
            $data = $this->filterDataByQueries($data, $queries);
            $resource = new AutoEnrichmentsResource($data);

            return $this->sendResponse($resource, 'Auto Enrichment fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AutoEnrichments  $autoEnrichments
     * @return \Illuminate\Http\Response
     */
    public function edit(AutoEnrichments $autoEnrichments)
    {
        //
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
            $autoEnrichmentsData = AutoEnrichmentsData::findOrFail($id);
            $autoEnrichmentsData->fill($request->all());
            $autoEnrichmentsData->save();

            return $this->sendResponse(new AutoEnrichmentsResource($autoEnrichmentsData), 'Auto Enrichment updated.');
        } catch(\Exeption $exception) {
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
            $autoEnrichmentsData = AutoEnrichmentsData::findOrFail($id);
            $autoEnrichmentsData->delete();

            return $this->sendResponse(new AutoEnrichmentsResource($autoEnrichmentsData), 'Auto Enrichment deleted.');
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

        $queryColumns = array(
            'Name' => 'Name',
            'Type' => 'Type',
            'WikiData' => 'WikiData',
            'StoryId' => 'StoryId',
            'ItemId' => 'ItemId',
            'AutoEnrichmentId' => 'AutoEnrichmentId',
            'CompletionStatus' => 'CompletionStatus'
        );

        $autoEnrichmentsData = new AutoEnrichmentsData();

        $data = $autoEnrichmentsData->whereRaw('1 = 1');

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
        $orderBy = $queries['orderBy'] ?? 'LastUpdated';
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
