<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Dataset;
use App\Http\Resources\DatasetResource;

class DatasetController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'Name' => 'Name',
            'ProjectId' => 'ProjectId'
        ];

        $initialSortColumn = 'DatasetId';

        $model = new Dataset();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = DatasetResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Datasets fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Dataset::findOrFail($id);
            $resource = new DatasetResource($data);

            return $this->sendResponse($resource, 'Dataset fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $dataset = new Dataset();
            $dataset->fill($request->all());
            $dataset->save();

            return $this->sendResponse(new DatasetResource($dataset), 'Dataset inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }


    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $dataset = Dataset::findOrfail($id);
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage(), 404);
        }

        try {
            $dataset->fill($request->all());
            $dataset->save();

            return $this->sendResponse(new DatasetResource($dataset), 'Dataset updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $dataset = Dataset::findOrfail($id);
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage(), 404);
        }

        try {
            $resource = $dataset->toArray();
            $resource = new DatasetResource($resource);
            $dataset->delete();

            return $this->sendResponse($resource, 'Dataset deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
