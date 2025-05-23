<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Project;
use App\Models\Story;
use App\Http\Resources\StoryResource;
use App\Http\Resources\CampaignResource;
use Illuminate\Validation\ValidationException;

class StoryController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'RecordId' => 'RecordId',
            'DcTitle' => 'dc:title',
            'ProjectId' => 'ProjectId',
            'DatasetId' => 'DatasetId',
            'StoryId' => 'StoryId'
        ];

        $initialSortColumn = 'StoryId';

        $model = new Story();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        $data = $this->filterDataByFieldlist($data, $request, ['StoryId'], $initialSortColumn);

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

            $validated = $request->validate([
                'Dc' => 'array',
                'Dcterms' => 'array',
                'Edm' => 'array',
            ]);

            $story->fill($request->all());

            $story->dc = $request['Dc'] ?? [];
            $story->dcterms = $request['Dcterms'] ?? [];
            $story->edm = $request['Edm'] ?? [];

            if ($story->ProjectId && !Project::find($story->ProjectId)) {
                throw ValidationException::withMessages(
                    ['ProjectId' => __('ProjectId does not exists')]
                );
            }

            $story->save();

            // LastUpdated is not updating correclty so we update it manually
            // can be removed when the issue is solved
            $story->touch();

            $resource = new StoryResource($story);

            return $this->sendResponse(new StoryResource($story), 'Story updated.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $story = Story::findOrfail($id);
            $resource = $story->toArray();
            $resource = new StoryResource($resource);
            $story->delete();

            return $this->sendResponse($resource, 'Story deleted.');
        } catch(\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    public function showCampaignsByStories(Request $request): JsonResponse
    {
        try {
            $storyIds = array_map('trim', explode(',', $request->input('StoryIds')));

            $stories = Story::whereIn('StoryId', $storyIds)
                ->select('StoryId')
                ->with(['campaigns' => function($query) {
                    $query->select('Campaign.CampaignId', 'Campaign.Name');
                }])
                ->get();

            $data = [];

            foreach ($stories as $story) {
                // Teams shouldn't be here only CampaignId and Name aselected above
                // so we hide from the response
                $story->campaigns->makeHidden('Teams');

                $data[] = [
                    'StoryId' => $story->StoryId,
                    'Campaigns' => $story->campaigns
                ];
            }

            $resource = new StoryResource($data);

            return $this->sendResponse($resource, 'Campaigns fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('An error occurred', $exception->getMessage());
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

    public function addCampaigns(Request $request, int $storyId): JsonResponse
    {
        try {
            $story = Story::findOrFail($storyId);

            if (is_array($request['Campaigns'])) {
                $story->campaigns()->syncWithoutDetaching($request['Campaigns']);
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
