<?php

namespace App\Services\Export;

use App\Models\Story;
use App\Models\Item;
use App\Models\PropertyType;
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
            'ItemIds',
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

        $itemArray['DescriptionLanguage'] = $this->transformLanguage(collect(
            [$itemArray['DescriptionLang']])
        );

        // reassign/cast values for readability and access
        $itemArray['CompletionStatus'] = $itemArray['CompletionStatus']['Name'];
        $itemArray['ImageLink'] = $this->extractImageLink($itemArray['ImageLink']);
        $itemArray['Transcription'] = $this->transformTranscription(
            collect($itemArray['Transcription'])
        );

        $itemArray['Properties'] = $this->transformProperties(
            collect($itemArray['Properties'])
        );

        // remove other nested attributes
        Arr::forget($itemArray, ['DescriptionLang']);

        return $itemArray;
    }

    private function transformProperties(Collection $properties): array
    {
        $hiddenElements = [
            'PropertyId',
            'PropertyTypeId',
            'PropertyTypeName',
            'Value',
        ];

        return $properties
            ->map(function (array $property) use ($hiddenElements) {
                $property['Name'] = $property['Value'];
                $property['Type'] = $property['PropertyTypeName'];

                Arr::forget($property, $hiddenElements);

                return $property;
            })
            ->toArray();
    }

    private function transformLanguage(Collection $languages): string
    {
        return implode(', ', $languages->pluck('NameEnglish')->toArray());
    }

    private function transformTranscription(Collection $transcription): array
    {
        $hiddenElements = [
            'UserId',
            'Text',
            'NoText',
            'CurrentVersion',
        ];

        $transcription->forget($hiddenElements);

        $transcription['Language'] = $this->transformLanguage(
            collect($transcription['Language'])
        );

        return $transcription->toArray();
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
