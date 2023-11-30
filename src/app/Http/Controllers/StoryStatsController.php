<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\ItemStats;
use App\Http\Resources\StoryStatsResource;

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

            $data = [];
            $data['StoryId'] = $id;
            $data['EditStart'] = $storyData->first()->EditStart; // oldest aka first from sorted collection
            $data['TranscribedCharsManual'] = $storyData->sum('TranscribedCharsManual');
            $data['TranscribedCharsHtr'] = $storyData->sum('TranscribedCharsHtr');
            $data['Timestamp'] = $storyData->min('Timestamp');
            $data['LastUpdated'] = $storyData->max('LastUpdated');
            $data['UserIds'] = [];
            $data['Enrichments'] = [
                'Places' => 0,
                'Persons' => 0,
                'Properties' => 0,
                'Dates' => 0,
                'Descriptions' => 0
            ];

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
