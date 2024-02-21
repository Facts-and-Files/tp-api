<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\StoryStatsResource;
use App\Models\Item;
use App\Models\ItemStats;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StoryStatsController extends ResponseController
{
    public function show(int $id): JsonResponse
    {
        try {
            $request = new Request([
                'limit' => 10000,
                'StoryId' => $id,
                'orderDir' => 'asc',
                'orderBy' => 'EditStart',
                'broadMatch' => false
            ]);
            $model = new ItemStats();
            $queryColumns = ['StoryId' => 'StoryId'];
            $initialSortColumn = 'ItemId';

            $storyData = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

            if ($storyData->count() <= 0) {
                return $this->sendError('Not found', 'No statistics exists for this storyId.');
            }

            // get oldest with data and not null
            $firstWithDate = $storyData->whereNotNull('EditStart')->first();

            $itemCompletions = Item::selectRaw('CompletionStatusId as StatusId, COUNT(*) as Amount')
                ->where('StoryId', $id)
                ->groupBy('StatusId')
                ->get();

            // replace custom properties of Item model with own from above
            $itemCompletions->each(function ($item) {
                $item->setAppends([]);
            });

            $data = [];
            $data['StoryId'] = $id;
            $data['EditStart'] = $firstWithDate ? $firstWithDate->EditStart : null;
            $data['TranscribedCharsManual'] = $storyData->sum('TranscribedCharsManual');
            $data['TranscribedCharsHtr'] = $storyData->sum('TranscribedCharsHtr');
            $data['Timestamp'] = $storyData->min('Timestamp');
            $data['ItemStatsDone'] = $storyData->count();
            $data['LastUpdated'] = $storyData->max('LastUpdated');
            $data['UserIds'] = [];
            $data['Enrichments'] = [
                'Places' => 0,
                'Persons' => 0,
                'Properties' => 0,
                'Dates' => 0,
                'Descriptions' => 0
            ];
            $data['CompletionStatus'] = $itemCompletions;

            $storyData->each(function ($item) use (&$data) {
                // get all unique item transcribers/users
                $data['UserIds'] = array_unique(array_merge($data['UserIds'], $item['UserIds']));

                // count all enrichments from story items
                foreach ($data['Enrichments'] as $key => $value) {
                  $data['Enrichments'][$key] += $item['Enrichments'][$key];
                }
            });

            $resource = new StoryStatsResource($data);

            return $this->sendResponse($resource, 'ItemStats fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

}
