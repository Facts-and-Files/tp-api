<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Story;
use App\Http\Resources\StoryResource;
use App\Http\Resources\CampaignResource;

class StoryController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $data = $this->getDataByRequest($request);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = StoryResource::collection($data);

        return $this->sendResponse($collection, 'Stories fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = Story::findOrFail($id);
            $resource = new StoryResource($data);

            return $this->sendResponse($resource, 'Story fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $story = Story::findOrfail($id);
            $story->fill($request->all());
            $story->save();

            $resource = new StoryResource($story);

            return $this->sendResponse(new StoryResource($story), 'Story updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function showCampaigns(int $storyId): JsonResponse
    {
        try {
            $story = Story::findOrFail($storyId);
            $campaigns = $story->campaigns;
            $data = $campaigns->map(function ($campaign) {
                return [
                    'CampaignId' => $campaign->CampaignId,
                    'Name' => $campaign->Name
                ];
            });

            $resource = new CampaignResource($data);

            return $this->sendResponse($resource, 'Campaigns fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function updateCampaigns(Request $request, int $storyId): JsonResponse
    {
        try {
            $story = Story::findOrFail($storyId);

            if (is_array($request['Campaigns'])) {
                $story->campaigns()->sync($request['Campaigns']);
            }

            $campaigns = $story->campaigns;
            $data = $campaigns->map(function ($campaign) {
                return [
                    'CampaignId' => $campaign->CampaignId,
                    'Name' => $campaign->Name
                ];
            });
            $resource = new CampaignResource($data);

            return $this->sendResponse($resource, 'Campaigns updated.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    protected function getDataByRequest(Request $request): Collection
    {
        $queries = $request->query();

        $queryColumns = [
            'RecordId' => 'RecordId'
        ];

        $story = new Story();

        $data = $story->whereRaw('1 = 1');

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
        $orderBy = $queries['orderBy'] ?? 'StoryId';
        $orderBy = $orderBy === 'id' ? 'StoryId' : $orderBy;
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
