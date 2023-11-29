<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ResponseController;
use App\Models\Item;
use App\Models\ItemStats;
use App\Models\Transcription;
use App\Http\Resources\ItemStatsResource;

class ItemStatsController extends ResponseController
{
    public function index(Request $request): JsonResponse
    {
        $queryColumns = [
            'StoryId' => 'StoryId'
        ];

        $initialSortColumn = 'LastUpdated';

        $model = new ItemStats();

        $data = $this->getDataByRequest($request, $model, $queryColumns, $initialSortColumn);

        if (!$data) {
            return $this->sendError('Invalid data', $request . ' not valid', 400);
        }

        $collection = ItemStatsResource::collection($data);

        return $this->sendResponseWithMeta($collection, 'ItemStats fetched.');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = ItemStats::findOrFail($id);
            $resource = new ItemStatsResource($data);

            return $this->sendResponse($resource, 'ItemStats fetched.');
        } catch (\Exception $exception) {
            return $this->sendError('Not found', $exception->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = [];

        try {
            foreach ($request['ItemIds'] as $itemId) {
                $item = Item::findOrfail($itemId);

                // transcription chars count
                $transcriptionCount = isset($item->Transcription->TranscriptionText)
                    ? mb_strlen($item->Transcription->TranscriptionText, 'UTF-8')
                    : 0;

                // create or update
                $itemStats = ItemStats::find($itemId) ?: new ItemStats();

                // populate other counts and stats
                $itemStats->ItemId = $item->ItemId;
                $itemStats->StoryId = $item->StoryId;
                $itemStats->Enrichments = $this->getEnrichmentsCounts($item);
                $itemStats->TranscribedCharsManual = $item->TranscriptionSource === 'manual' ? $transcriptionCount : 0;
                $itemStats->TranscribedCharsHtr = $item->TranscriptionSource === 'htr' ? $transcriptionCount : 0;
                $itemStats->EditStart = $this->getOldestTranscriptionDate($item);
                $itemStats->UserIds = $this->getUserIds($item);

                $itemStats->save();

                $data[] = $itemStats;
            }

            $resource = new ItemStatsResource($data);

            return $this->sendResponse($resource, 'ItemStats updated.');
        } catch (\Exception $exception) {
            return $this->sendError('Invalid data', $exception->getMessage(), 400);
        }
    }

    protected function getUserIds(Item $item): array
    {
       $manualUserIds = $item
            ->transcriptions()
            ->select('UserId')
            ->groupBy('UserId')
            ->get()
            ->pluck('UserId')
            ->toArray();

        $htrUserIds = $item
            ->htrData()
            ->with('htrDataRevision')
            ->get()
            ->flatMap(function ($htrData) {
                return $htrData
                    ->htrDataRevision
                    ->pluck('UserId')
                    ->filter(function ($userId) {
                        return $userId !== null;
                    })
                ->unique();
            })
            ->toArray();

        $merged = array_unique(array_merge($manualUserIds, $htrUserIds));

        return $merged;
    }

    protected function getEnrichmentsCounts(Item $item): array
    {
        $enrichments = [];
        $enrichments['Descriptions'] = !empty($item->Description) ? 1 : 0;
        $enrichments['Places'] = $item->Places->count();
        $enrichments['Persons'] = $item->Persons->count();
        $enrichments['Properties'] = $item->Properties->count();
        $enrichments['Dates'] = $this->countDates($item);

        return $enrichments;
    }

    protected function getOldestTranscriptionDate(Item $item): string|null
    {
        // manual transcription begin date
        $editStartManual = $item
            ->transcriptions()
            ->orderBy('Timestamp', 'asc')
            ->first('Timestamp');

        // htr transcription begin date
        $editStartHtr = $item
            ->htrData()
            ->with(['htrDataRevision' => function ($query) {
                $query
                    ->orderBy('Timestamp', 'asc')
                    ->first();
            }])
            ->orderBy('Timestamp', 'asc')
            ->first('Timestamp');

        $editStartHtrDatetime = $editStartHtr
            ? date('Y-m-d H:i:s', strtotime($editStartHtr->Timestamp))
            : null;

        $editStartManualDatetime = $editStartManual
            ? date('Y-m-d H:i:s', strtotime($editStartManual->Timestamp))
            : null;

        // if both dates exists retrun oldest
        if ($editStartManualDatetime && $editStartHtrDatetime) {
            return $editStartManualDatetime < $editStartHtrDatetime
                ? $editStartManualDatetime
                : $editStartHtrDatetime;
        }

        // otherwise return existent or null
        return $editStartManualDatetime ?: $editStartHtrDatetime ?: null;
    }

    protected function countDates(Item $item): int
    {
        $count = 0;

        $count += !empty($item->DateStartDisplay) ? 1 : 0;
        $count += !empty($item->DateEndDisplay) ? 1 : 0;

        return $count;
    }
}
