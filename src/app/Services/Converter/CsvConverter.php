<?php

namespace App\Services\Converter;

use App\Models\Item;
use App\Models\Story;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CsvConverter implements ConverterInterface
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
        'Properties',
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

    public function convertItemProperties(Collection $itemIds): array
    {
        $properties = [];
        $itemsCollection = Item::whereIn('ItemId', $itemIds)->orderBy('OrderIndex')->get();

        foreach ($itemsCollection as $item) {
            foreach ($item->Properties as $property) {
                $properties[] = [
                    'ItemId' => $item->ItemId,
                    'Type' => $property['PropertyTypeName'],
                    'Name' => $property['Value'],
                    'Description' => $property['Description'] ?? '',
                ];
            }
        }

        return $properties;
    }

    private function convertItem(Item $item, array $exclude = []): array
    {
        $itemArray = $item->makeHidden([...$this->itemHiddenElements, ...$exclude])->toArray();

        $itemArray['CompletionStatus'] = $itemArray['CompletionStatus']['Name'];
        $itemArray['ImageLink'] = $this->extractImageLink($itemArray['ImageLink']);

        $itemArray['Description.Text'] = $itemArray['Description'];
        $itemArray['Description.Language'] = $this->convertLanguage(
            collect([$itemArray['DescriptionLang']]),
        );

        $transcription = collect($itemArray['Transcription']);
        $itemArray['Transcription.Language'] = $this->convertLanguage(
            collect($transcription['Language'] ?? []),
        );

        foreach ($transcription as $key => $value) {
            if (!in_array($key, ['UserId', 'Text', 'NoText', 'CurrentVersion', 'Language'])) {
                $itemArray["Transcription.{$key}"] = $value;
            }
        }

        Arr::forget($itemArray, [
            'Description',
            'DescriptionLang',
            'Transcription',
        ]);

        return $itemArray;
    }

    private function convertLanguage(Collection $languages): string
    {
        return implode(', ', $languages->pluck('NameEnglish')->toArray());
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
