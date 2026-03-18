<?php

namespace App\Services\Converter;

use App\Models\Item;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class YamlConverter extends AbstractDataConverter
{
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

    protected function convertItem(Item $item, array $exclude = []): array
    {
        $itemArray = $item->makeHidden([...$this->itemHiddenElements, ...$exclude])->toArray();

        $itemArray['CompletionStatus'] = $itemArray['CompletionStatus']['Name'];
        $itemArray['ImageLink']        = $this->extractIiifImageLink($itemArray['ImageLink']);

        $itemArray['Description'] = [
            'Text'     => $itemArray['Description'],
            'Language' => $this->convertLanguage(collect([$itemArray['DescriptionLang']])),
        ];

        $itemArray['Transcription'] = $this->convertTranscription(collect($itemArray['Transcription']));
        $itemArray['Properties']    = $this->convertProperties(collect($itemArray['Properties']));

        Arr::forget($itemArray, ['DescriptionLang']);

        return $itemArray;
    }

    private function convertTranscription(Collection $transcription): array
    {
        $transcription->forget($this->transcriptionHiddenElements);
        $transcription['Language'] = $this->convertLanguage(collect($transcription['Language']));

        return $transcription->toArray();
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
}
