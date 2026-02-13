<?php

namespace App\Services\Export;

use App\Models\Story;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\StreamedResponse;
use League\Csv\Writer;
use ZipStream\ZipStream;

class CsvStoryExporter implements StoryExporterInterface
{
    public function __construct(
        private CsvTransformer $csvTransformer
    ) {}

    public function export(Story $story): StreamedResponse
    {
        $storyId = $story->StoryId;
        $baseStoryFilename = "story-{$storyId}-" . now()->format('Ymd-His');
        $baseItemsFilename = "story-items-{$storyId}-" . now()->format('Ymd-His');
        $basePropertiesFilename = "story-properties-{$storyId}-" . now()->format('Ymd-His');
        $zipName = $baseStoryFilename . '.zip';

        return response()->streamDownload(
            callback: function () use (
                $baseItemsFilename,
                $baseStoryFilename,
                $basePropertiesFilename,
                $story,
                $zipName,
            ) {
                $zip = new ZipStream(
                    outputName: $zipName,
                    defaultEnableZeroHeader: true,
                    contentType: 'application/octet-stream',
                );

                $zip->addFile("{$baseStoryFilename}.csv", $this->formatStoryAsCsv($story));
                $zip->addFile("{$baseItemsFilename}.csv", $this->formatStoryItemsAsCsv($story));
                $zip->addFile("{$basePropertiesFilename}.csv", $this->formatItemPropertiesAsCsv($story));

                $zip->finish();
            },
            name: $zipName,
            headers: [
                'Content-Type' => 'application/zip; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$zipName.'"',
            ]
        );
    }

    private function formatStoryAsCsv(Story $story): string
    {
        $storyData = $this->csvTransformer->transformStory($story, []);
        return $this->buildCsvFromSingleRecord($storyData);
    }

    private function formatStoryItemsAsCsv(Story $story): string
    {
        $storyItemsData = $this->csvTransformer->transformItems($story->ItemIds);
        return $this->buildCsvFromMultipleRecords($storyItemsData['Items']);
    }

    private function formatItemPropertiesAsCsv(Story $story): string
    {
        $properties = $this->csvTransformer->transformItemProperties($story->ItemIds);

        if (empty($properties)) {
            return '';
        }

        return $this->buildCsvFromMultipleRecords($properties);
    }

    private function buildCsvFromSingleRecord(array $array): string
    {
        if (empty($array)) {
            return '';
        }

        $flattenedData = Arr::dot($array);
        $csv = Writer::fromString();
        $csv->insertOne(array_keys($flattenedData));
        $csv->insertOne(array_values($flattenedData));

        return $csv->toString();
    }

    private function buildCsvFromMultipleRecords(array $records): string
    {
        if (empty($records)) {
            return '';
        }

        $csvData = $this->buildHeaderAndRows($records);
        return $this->writeCsv($csvData['headers'], $csvData['rows']->toArray());
    }

    private function buildHeaderAndRows(array $data): array
    {
        $csvData = [];
        $csvData['rows'] = collect($data)->map(fn($item) => Arr::dot($item));
        $csvData['headers'] = $csvData['rows']
            ->flatMap(fn($row) => array_keys($row))
            ->unique()
            ->values()
            ->all();

        return $csvData;
    }

    private function writeCsv(array $headers, array $rows): string
    {
        $csv = Writer::fromString();
        $csv->insertOne($headers);
        $csv->insertAll($rows);

        return $csv->toString();
    }
}
