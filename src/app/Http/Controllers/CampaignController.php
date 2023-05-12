<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Campaign;
use App\Http\Resources\CampaignResource;

class CampaignController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $data = $this->getDataByRequest($request);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = CampaignResource::collection($data);

        return $this->sendResponse($collection, 'Campaigns fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Campaign::findOrFail($id);
            $resource = new CampaignResource($data);

            return $this->sendResponse($resource, 'Campaign fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $campaign = new Campaign();
            $campaign->fill($request->all());
            $campaign->save();

            if (is_array($request['TeamIds'])) {
                $campaign->team()->sync($request['TeamIds']);
            }

            return $this->sendResponse(new CampaignResource($campaign), 'Campaign inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $campaign = Campaign::findOrfail($id);
            $campaign->fill($request->all());
            $campaign->save();

            if (is_array($request['TeamIds'])) {
                $campaign->team()->sync($request['TeamIds']);
            }

            return $this->sendResponse(new CampaignResource($campaign), 'Campaign updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $campaign = Campaign::findOrfail($id);
            $resource = $campaign->toArray();
            $resource = new CampaignResource($resource);
            $campaign->delete();

            return $this->sendResponse($resource, 'Campaign deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    protected function getDataByRequest(Request $request): Collection
    {
        $queries = $request->query();

        $queryColumns = [
            'Name' => 'Name',
            'DatasetId' => 'DatasetId'
        ];

        $campaign = new Campaign();

        $data = $campaign->whereRaw('1 = 1');

        foreach ($queries as $queryName => $queryValue) {
            if (array_key_exists($queryName, $queryColumns)) {
                $data->where($queryColumns[$queryName], $queryValue);
            }
        }

        $data = $this->filterDataByQueries($data, $queries);

        return $data;
    }

    protected function filterDataByQueries(Builder $data, array $queries): Collection
    {
        $limit = $queries['limit'] ?? 100;
        $page = $queries['page'] ?? 1;
        $orderBy = $queries['orderBy'] ?? 'CampaignId';
        $orderBy = $orderBy === 'id' ? 'CampaignId' : $orderBy;
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
