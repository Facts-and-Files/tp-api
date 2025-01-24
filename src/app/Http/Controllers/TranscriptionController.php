<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Transcription;
use App\Http\Resources\TranscriptionResource;

class TranscriptionController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'ItemId' => 'ItemId',
            'UserId' => 'UserId',
            'EuropeanaAnnotationId' => 'EuropeanaAnnotationId',
            'CurrentVersion' => 'CurrentVersion',
        ];

        $initialSortColumn = 'TranscriptionId';

        $model = new Transcription();

        if ($request['CurrentVersion']) {
            $request['CurrentVersion'] = $request->boolean('CurrentVersion');
        }

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = TranscriptionResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Transcriptions fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Transcription::findOrFail($id);
            $resource = new TranscriptionResource($data);

            return $this->sendResponse($resource, 'Transcription fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = new Transcription();

            $data->fill($request->all());

            $data->CurrentVersion = true;
            $data->save();

            Transcription::where('ItemId', $data->ItemId)
                ->where('CurrentVersion', '=', true)
                ->where('TranscriptionId', '!=', $data->TranscriptionId)
                ->update(['CurrentVersion' => false]);

            return $this->sendResponse(new TranscriptionResource($data), 'Transcription inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
