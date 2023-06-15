<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Project;
use App\Http\Resources\ProjectResource;

class ProjectController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $data = $this->getDataByRequest($request);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = ProjectResource::collection($data);

        return $this->sendResponse($collection, 'Projects fetched.');
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
            $resource = $data->toArray();
            $resource = new ProjectResource($resource);
            $data->delete();

            return $this->sendResponse($resource, 'Project deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    protected function getDataByRequest(Request $request): Collection
    {
        $queries = $request->query();

        $queryColumns = [
            'Name' => 'Name'
        ];

        $project = new Project();

        $data = $project->whereRaw('1 = 1');

        foreach ($queries as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queryColumns)) {
                $data->where($queryColumns[$queryName], $queryValue);
            }
        }

        $data = $this->filterDataByQueries($data, $queries);

        return $data;
    }

    protected function filterDataByQueries(Builder $data, Array $queries): Collection
    {
        $limit = $queries['limit'] ?? 100;
        $page = $queries['page'] ?? 1;
        $orderBy = $queries['orderBy'] ?? 'ProjectId';
        $orderBy = $orderBy === 'id' ? 'ProjectId' : $orderBy;
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
