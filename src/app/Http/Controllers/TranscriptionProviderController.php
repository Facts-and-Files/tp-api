<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\TranscriptionProvider;
use App\Http\Resources\TranscriptionProviderResource;

class TranscriptionProviderController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [];

        $initialSortColumn = 'TranscriptionProviderId';

        $model = new TranscriptionProvider();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = TranscriptionProviderResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'TranscriptionProviders fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = TranscriptionProvider::findOrFail($id);
            $resource = new TranscriptionProviderResource($data);

            return $this->sendResponse($resource, 'TranscriptionProvider fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = new TranscriptionProvider();
            $data->fill($request->all());
            $data->save();

            return $this->sendResponse(new TranscriptionProviderResource($data), 'Transcription provider inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }


    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = TranscriptionProvider::findOrfail($id);
        } catch(\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage(), 404);
        }

        try {
            $data->fill($request->all());
            $data->save();

            return $this->sendResponse(new TranscriptionProviderResource($data), 'Transcription provider updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $data = TranscriptionProvider::findOrfail($id);
        } catch(\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage(), 404);
        }

        try {
            $resource = $data->toArray();
            $resource = new TranscriptionProviderResource($resource);
            $data->delete();

            return $this->sendResponse($resource, 'Transcription provider deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
