<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Events\ScoreTableUpdated;
use App\Models\Campaign;
use App\Models\Item;
use App\Models\Score;
use App\Models\Story;
use App\Http\Resources\ScoreResource;

class ScoreController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'ItemId'      => 'ItemId',
            'UserId'      => 'UserId',
            'ScoreTypeId' => 'ScoreTypeId'
        ];

        $initialSortColumn = 'Timestamp';

        $model = new Score();

        $storyIds = $request['StoryId'] ? [$request['StoryId']] : [];
        $itemIds = $request['ItemId'] ? [$request['ItemId']] : [];

        if ($campaignId = $request['CampaignId']) {
            $campaign = Campaign::find($campaignId);

            if (!$campaign) {
                return $this->sendError('Not found', 'Campaign not found.');
            }

            $storyIds = $campaign->StoryIds->toArray();
        }

        if ($storyId = $request['StoryId']) {
            $story = Story::find($storyId);
            if (!$story) {
                return $this->sendError('Not found', 'Story not found.');
            }
        }

        if (count($storyIds) > 0) {
            $itemIds = Item::whereIn('StoryId', $storyIds)->pluck('ItemId')->toArray();
            $request['ItemId'] = implode(',', $itemIds);
            $request['separator'] = ',';
        }

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = ScoreResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'Scores fetched.');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $score = new Score();

            if (!isset($request['Amount']) || $request['Amount'] <= 0) {
                return $this->sendResponse(new ScoreResource($score), 'Nothing to insert.');
            }

            $score->fill($request->all());
            $score->save();

            ScoreTableUpdated::dispatch($score);

            return $this->sendResponse(new ScoreResource($score), 'Score inserted.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }
}
