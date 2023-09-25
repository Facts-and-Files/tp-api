<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Team;
use App\Http\Resources\TeamResource;

class TeamController extends ResponseController
{
    protected $meta = [];

    public function index(Request $request): JsonResponse
    {
        $data = $this->getDataByRequest($request);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = TeamResource::collection($data);

        return $this->sendResponseWithMeta($collection, $this->meta, 'Teams fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Team::findOrFail($id);
            $resource = new TeamResource($data);

            return $this->sendResponse($resource, 'Team fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $team = new Team();
            $team->fill($request->all());
            $team->save();

            if (is_array($request['UserIds'])) {
                $team->user()->sync($request['UserIds']);
            }

            $resource = new TeamResource($team);

            return $this->sendResponse($resource, 'Team inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $team = Team::findOrfail($id);
            $team->fill($request->all());
            $team->save();

            if (is_array($request['UserIds'])) {
                $team->user()->sync($request['UserIds']);
            }

            $resource = new TeamResource($team);

            return $this->sendResponse(new TeamResource($team), 'Team updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $team = Team::findOrfail($id);
            $resource = $team->toArray();
            $resource = new TeamResource($resource);
            $team->delete();

            return $this->sendResponse($resource, 'Team deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    protected function getDataByRequest(Request $request): Collection
    {
        $queries = $request->query();

        // $broadMatch = $queries['broadMatch'] ?? false;
        $broadMatch =  empty($queries['broadMatch'])
            ? false
            : filter_var($queries['broadMatch'], FILTER_VALIDATE_BOOLEAN);

        $queryColumns = [
            'Name' => 'Name',
            'ShortName' => 'ShortName'
        ];

        $team = new Team();

        $data = $team->whereRaw('1 = 1');

        foreach ($queries as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queryColumns)) {
                if ($broadMatch === false) {
                    $data->where($queryColumns[$queryName], $queryValue);
                } else {
                    $data->where($queryColumns[$queryName], 'LIKE', '%' . $queryValue . '%');
                }
            }
        }

        $data = $this->filterDataByQueries($data, $queries);

        return $data;
    }

    protected function filterDataByQueries(Builder $data, array $queries): Collection
    {
        $limit = $queries['limit'] ?? 100;
        $page = $queries['page'] ?? 1;
        $orderBy = $queries['orderBy'] ?? 'TeamId';
        $orderBy = $orderBy === 'id' ? 'TeamId' : $orderBy;
        $orderDir = $queries['orderDir'] ?? 'asc';
        $offset = $limit * ($page - 1);

        $this->meta = [
            'limit' => (int) $limit,
            'currentPage' => (int) $page,
            'lastPage' => ceil($data->count() / $limit),
            'fromEntry' => ($page - 1) * $limit + 1,
            'toEntry' => min($page * $limit, $data->count()),
            'totalEntries' => $data->count()
        ];

        $filtered = $data
            ->limit($limit)
            ->offset($offset)
            ->orderBy($orderBy, $orderDir)
            ->get();

        return $filtered;
    }
}
