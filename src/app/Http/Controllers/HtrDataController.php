<?php

namespace App\Http\Controllers;

use SimpleXMLElement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\ResponseController;
use App\Models\HtrData;
use App\Models\HtrDataRevision;
use App\Http\Resources\HtrDataResource;

class HtrDataController extends ResponseController
{
    /**
     * Display a paginated listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->getDataByRequest($request);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = HtrDataResource::collection($data);

        return $this->sendResponse($collection, 'HtrData fetched.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $htrData = new HtrData();
            $htrData->fill($request->all());
            $htrData->ItemId = $request->ItemId;
            $htrData->save();

            if (is_array($request['Language'])) {
                $htrData->language()->sync($request['Language']);
            }

            $htrDataRevision = new HtrDataRevision();
            $htrDataRevision->fill($request->all());
            $htrDataRevision->HtrDataId = $htrData->HtrDataId;
            $htrDataRevision->TranscriptionText = $request['TranscriptionText']
                ? $request['TranscriptionText']
                : ($request['TranscriptionData']
                    ? $this->getTextFromPageXml($request['TranscriptionData'], "\n")
                    : '');
            $htrDataRevision->save();

            $resource = new HtrDataResource($htrData);

            return $this->sendResponse($resource, 'HtrData inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $data = HtrData::findOrFail($id);
            $resource = new HtrDataResource($data);

            return $this->sendResponse($resource, 'HtrData fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int      $userId
     * @param  Request  $request
     * @return JsonResponse
     */
    public function showByItemId(int $itemId, Request $request): JsonResponse
    {
        try {
            $queries = $request->query();
            $data = HtrData::where('ItemId', $itemId);
            $data = $this->filterDataByQueries($data, $queries);
            $resource = new HtrDataResource($data);

            return $this->sendResponse($resource, 'HtrData fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $htrData = HtrData::findOrfail($id);
            $htrData->fill($request->all());
            $htrData->save();

            if (is_array($request['Language'])) {
                $htrData->language()->sync($request['Language']);
            }

            // we need at least the XML TranscriptionData for creation of a new revision
            if (!empty($request['TranscriptionData'])) {
                $htrDataRevision = new HtrDataRevision();
                $htrDataRevision->fill($request->all());
                $htrDataRevision->HtrDataId = $htrData->HtrDataId;
                $htrDataRevision->TranscriptionText = $request['TranscriptionText']
                    ? $request['TranscriptionText']
                    : ($request['TranscriptionData']
                        ? $this->getTextFromPageXml($request['TranscriptionData'], "\n")
                        : '');
                $htrDataRevision->save();
            }

            $resource = new HtrDataResource($htrData);

            return $this->sendResponse(new HtrDataResource($htrData), 'HtrData updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $htrData = HtrData::findOrfail($id);
            $resource = $htrData->toArray();
            $resource = new HtrDataResource($resource);
            $htrData->delete();

            return $this->sendResponse($resource, 'HtrData deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    /**
     * Get data defined by request
     *
     * @param  Request  $request
     * @return Collection
     */
    protected function getDataByRequest(Request $request): Collection
    {
        $queries = $request->query();

        $queryColumns = array(
            'HtrProcessId' => 'HtrProcessId',
            'ItemId' => 'ItemId',
            'HtrModelId' => 'HtrModelId',
            'HtrStatus' => 'HtrStatus',
            'EuropeanaAnnotationId' => 'EuropeanaAnnotationId'
        );

        $data = HtrData::whereRaw('1 = 1');

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
     * @param  Builder $data
     * @param  array   $queries
     * @return Collection
     */
    protected function filterDataByQueries(Builder $data, Array $queries): Collection
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

    public function getTextFromPageXml(String $xmlString, String $break = ''): String
    {
        $text = '';
        $xmlString = str_replace('xmlns=', 'ns=', $xmlString);

        if (!empty($xmlString)) {
            $xml = new SimpleXMLElement($xmlString);
            $textRegions = $xml->xpath('//Page/TextRegion');

            foreach ($textRegions as $textRegion) {
                $textLines = $textRegion->xpath('TextLine');
                foreach ($textLines as $textLine) {
                    $textElement = $textLine->xpath(('TextEquiv/Unicode'));
                    $text .= $textElement[0] . $break;
                }
                $text .= $break;
            }
        }
        return $text;
    }
}
