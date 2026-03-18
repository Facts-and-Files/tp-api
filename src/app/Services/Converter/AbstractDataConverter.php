<?php

namespace App\Services\Converter;

use App\Models\Item;
use App\Models\Story;
use App\Traits\ExtractIiifImageLink;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class AbstractDataConverter implements ConverterInterface
{
    use ExtractIiifImageLink;

    protected array $storyHiddenElements = [
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

        foreach ($itemsCollection as $item) {
            $items['Items'][] = $this->convertItem($item, $exclude);
        }

        return $items;
    }

    abstract protected function convertItem(Item $item, array $exclude = []): array;
}
