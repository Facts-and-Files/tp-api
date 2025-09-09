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

    public function show($id): JsonResponse
    {
        $data = HtrData::findOrFail($id);
        $resource = new HtrDataResource($data);

        return $this->sendResponse($resource, 'HtrData fetched.');
    }

    public function showByItemId(int $itemId, Request $request): JsonResponse
    {
        Item::findOrFail($itemId);

        $queries = $request->query();
        $data = HtrData::where('ItemId', $itemId);
        $data = $this->filterDataByQueries($data, $queries, 'LastUpdated')->get();
        $resource = new HtrDataResource($data);

        return $this->sendResponseWithMeta($resource, 'HtrData fetched.');
    }

    public function showActiveByItemId(int $itemId): JsonResponse
    {
        Item::findOrFail($itemId);

        $queries = [
            'limit'    => 1,
            'page'     => 1,
            'orderBy'  => 'LastUpdated',
            'orderDir' => 'desc',
            'offset'   => 0
        ];
        $data = HtrData::where(['ItemId' => $itemId, 'HtrStatus' => 'FINISHED']);
        $latest = $this->filterDataByQueries($data, $queries, 'LastUpdated')->first();
        $resource = new HtrDataResource($latest);

        return $this->sendResponse($resource, 'HtrData fetched.');
    }

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
