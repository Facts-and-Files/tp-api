<?php

namespace App\Services\Converter;

use App\Models\Item;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CsvConverter extends AbstractDataConverter
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
        'Properties',
    ];

    public function convertItemProperties(Collection $items): array
    {
        $properties = [];

        foreach ($items as $item) {
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

    protected function convertItem(Item $item, array $exclude = []): array
    {
        $itemArray = $item->makeHidden([...$this->itemHiddenElements, ...$exclude])->toArray();

        $itemArray['CompletionStatus'] = $itemArray['CompletionStatus']['Name'];
        $itemArray['ImageLink'] = $this->extractIiifImageLink($itemArray['ImageLink']);
        $itemArray['Description.Text'] = $itemArray['Description'];
        $itemArray['Description.Language'] = $this->convertLanguage(collect([$itemArray['DescriptionLang']]));

        $transcription = collect($itemArray['Transcription']);
        $itemArray['Transcription.Language'] = $this->convertLanguage(collect($transcription['Language'] ?? []));

        foreach ($transcription as $key => $value) {
            if (!in_array($key, ['UserId', 'Text', 'NoText', 'CurrentVersion', 'Language'])) {
                $itemArray["Transcription.{$key}"] = $value;
            }
        }

        Arr::forget($itemArray, ['Description', 'DescriptionLang', 'Transcription']);

        return $itemArray;
    }

    private function convertLanguage(Collection $languages): string
    {
        return implode(', ', $languages->pluck('NameEnglish')->toArray());
    }
}
