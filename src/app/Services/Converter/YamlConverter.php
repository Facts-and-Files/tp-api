<?php

namespace App\Services\Converter;

use App\Models\Item;
use App\Models\Story;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class YamlConverter implements ConverterInterface
{
    private array $storyHiddenElements = [
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

    private array $itemHiddenElements = [
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

    private array $propertyHiddenElements = [
        'PropertyId',
        'PropertyTypeId',
        'PropertyTypeName',
        'Value',
    ];

    private array $transcriptionHiddenElements = [
        'UserId',
        'Text',
        'NoText',
        'CurrentVersion',
    ];

    public function convertStory(Story $story, array $exclude = []): array
    {
        $completionStatus = $story->CompletionStatus->Name;

        $data = $story->makeHidden([...$this->storyHiddenElements, ...$exclude])->toArray();
        $data['CompletionStatus'] = $completionStatus;

        Arr::forget($data, [
            'Place.UserId',
            'Place.UserGenerated',
        ]);

        return $data;
    }

    public function convertItems(Collection $itemIds, array $exclude = []): array
    {
        $items = [];
        $itemsCollection = Item::whereIn('ItemId', $itemIds)->orderBy('OrderIndex')->get();

        foreach ($itemsCollection as $itemCollection) {
            $items['Items'][] = $this->convertItem($itemCollection, $exclude);
        }

        return $items;
    }

    private function convertItem(Item $item, array $exclude = []): array
    {
        $itemArray = $item->makeHidden([...$this->itemHiddenElements, ...$exclude])->toArray();

        $itemArray['CompletionStatus'] = $itemArray['CompletionStatus']['Name'];
        $itemArray['ImageLink'] = $this->extractImageLink($itemArray['ImageLink']);

        $itemArray['Description'] = [
            'Text' => $itemArray['Description'],
            'Language' => $this->convertLanguage(collect([$itemArray['DescriptionLang']])),
        ];

        $itemArray['Transcription'] = $this->convertTranscription(
            collect($itemArray['Transcription']),
        );

        $itemArray['Properties'] = $this->convertProperties(
            collect($itemArray['Properties']),
        );

        Arr::forget($itemArray, ['DescriptionLang']);

        return $itemArray;
    }

    private function convertProperties(Collection $properties): array
    {
        return $properties
            ->map(function (array $property) {
                $property['Name'] = $property['Value'];
                $property['Type'] = $property['PropertyTypeName'];
                Arr::forget($property, $this->propertyHiddenElements);
                return $property;
            })
            ->toArray();
    }

    private function convertLanguage(Collection $languages): array
    {
        return $languages->pluck('NameEnglish')->toArray();
    }

    private function convertTranscription(Collection $transcription): array
    {
        $transcription->forget($this->transcriptionHiddenElements);
        $transcription['Language'] = $this->convertLanguage(
            collect($transcription['Language']),
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
