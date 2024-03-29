<?php

namespace App\Http\Controllers;

use SimpleXMLElement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\HtrData;
use App\Models\HtrDataRevision;
use App\Models\Transcription;
use App\Models\Item;
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
        $queryColumns = [
            'HtrProcessId' => 'HtrProcessId',
            'ItemId' => 'ItemId',
            'HtrModelId' => 'HtrModelId',
            'HtrStatus' => 'HtrStatus',
            'EuropeanaAnnotationId' => 'EuropeanaAnnotationId'
        ];

        $initialSortColumn = 'LastUpdated';

        $model = new HtrData();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = HtrDataResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'HtrData fetched.');
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
            $data = $this->filterDataByQueries($data, $queries, 'LastUpdated');
            $resource = new HtrDataResource($data);

            return $this->sendResponseWithMeta($resource, 'HtrData fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function showActiveByItemId(int $itemId): JsonResponse
    {
        try {
            $queries = [
                'limit'    => 1,
                'page'     => 1,
                'orderBy'  => 'LastUpdated',
                'orderDir' => 'desc',
                'offset'   => 0
            ];
            $data = HtrData::where(['ItemId' => $itemId, 'HtrStatus' => 'FINISHED']);
            $data = $this->filterDataByQueries($data, $queries, 'LastUpdated');
            $resource = new HtrDataResource($data[0]);

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

            // try to update TranscriptionSource and TranscriptionStatusId if
            // HTR is an only transcription
            if ($request->HtrStatus === 'FINISHED') {
                $this->setItemCompletionStatus($htrData->ItemId, 2);
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

    protected function setItemCompletionStatus (int $itemId, int $status): void
    {
            $transcription = Transcription::where([
                'ItemId' => $itemId,
                'CurrentVersion' => 1
            ])->get();

            $item = Item::find($itemId);


            if (
                count($transcription) === 0 and // no manual transcription available
                $item->TranscriptionSource === 'manual' and // transcription source is also manual
                $item->TranscriptionStatusId < 2 // and transcription status is also default 1
            ) {
                $item->TranscriptionSource = 'htr';
                $item->TranscriptionStatusId = 3;
                $item->save();
            }
    }
}
