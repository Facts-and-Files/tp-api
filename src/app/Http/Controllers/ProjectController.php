<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Project;
use App\Http\Resources\ProjectResource;

class ProjectController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'Name' => 'Name'
        ];

        $initialSortColumn = 'ProjectId';

        $model = new Project();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = ProjectResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Projects fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Project::findOrFail($id);
            $resource = new ProjectResource($data);

            return $this->sendResponse($resource, 'Project fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = new Project();
            $data->fill($request->all());
            $data->save();

            return $this->sendResponse(new ProjectResource($data), 'Project inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }


    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = Project::findOrfail($id);
        } catch(\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage(), 404);
        }

        try {
            $data->fill($request->all());
            $data->save();

            return $this->sendResponse(new ProjectResource($data), 'Project updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $data = Project::findOrfail($id);
        } catch(\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage(), 404);
        }

        try {
            $resource = $data->toArray();
            $resource = new ProjectResource($resource);
            $data->delete();

            return $this->sendResponse($resource, 'Project deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
