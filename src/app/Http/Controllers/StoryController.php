<?php

namespace App\Http\Controllers;

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
        $queryColumns = [
            'RecordId' => 'RecordId',
            'DcTitle' => 'dc:title'
        ];

        $initialSortColumn = 'StoryId';

        $model = new Story();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = StoryResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Stories fetched.');
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
}
