<?php

namespace App\Services\Export;

use App\Models\Story;
use App\Models\Item;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ModelTransformer
{
    public function transformStory(Story $story, array $exclude = []): array
    {
        $completionStatus = $story->CompletionStatus->Name;

        $hiddenElements = [
            'placeZoom',
            'PlaceName',
            'PlaceLatitude',
            'PlaceLongitude',
            'PlaceLink',
            'PlaceComment',
            'PlaceUserId',
            'PlaceUserGenerated',
            'PreviewImage',
            'DatasetId',
            'ProjectId',
            'HasHtr',
            'Public',
            'LastUpdated',
            'Timestamp',
            'CompletionStatus',
        ];

        // just enhance current story model's hidden attribute
        $data = $story->makeHidden([...$hiddenElements, ...$exclude])->toArray();

        // reassign only name/text of CompletionStatus
        $data['CompletionStatus'] = $completionStatus;

        // remove other nested attributes
        Arr::forget($data, [
            'Place.UserId',
            'Place.UserGenerated',
        ]);

        return $data;
    }

    public function addItemData(Collection $itemIds): array
    {
        $items = [];

        $itemsCollection = Item::whereIn('ItemId', $itemIds)->orderBy('OrderIndex')->get();

        foreach ($itemsCollection as $itemCollection) {
            $items['Items'][] = $this->transformItem($itemCollection);
        }

        return $items;
    }

    private function transformItem(Item $item): array
    {
        $hiddenElements = [
            'StoryId',
            'TranscriptionStatusId',
            'TaggingStatusId',
            'LocationStatusId',
            'ProjectItemId',
            'OrderIndex',
            'LastUpdated',
            'Timestamp',
            'Properties',
            'DescriptionStatusId',
            'AutomaticEnrichmentStatusId',
            'Manifest',
            'LockedTime',
            'LockedUser',
            'DateStartDisplay',
            'DateEndDisplay',
            'DateRole',
        ];

        // just enhance current item model's hidden attribute and cast as array
        $itemArray = $item->makeHidden($hiddenElements)->toArray();

        $itemArray['DescriptionLanguage'] = !empty($itemArray['DescriptionLang'])
            ? $itemArray['DescriptionLang']
            : '';

        // reassign only name/text of CompletionStatus
        $itemArray['CompletionStatus'] = $itemArray['CompletionStatus']['Name'];

        $itemArray['ImageLink'] = $this->extractImageLink($itemArray['ImageLink']);

        // remove other nested attribute
        Arr::forget($itemArray, [
            'Transcription.UserId',
            'Transcription.Text',
            'Transcription.NoText',
            'Transcription.CurrentVersion',
            'DescriptionLang',
        ]);

        return $itemArray;
    }

    private function extractImageLink(string $imageDataCollection): string
    {
        $imageData = json_decode($imageDataCollection, true);

        $link = !empty($imageData) ? $imageData['@id'] : '';

        return ($link !== '' && !str_starts_with($link, 'http'))
            ? "https://{$link}"
            : $link;
    }
}
