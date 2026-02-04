<?php

namespace App\Services\Export;

use App\Models\Story;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\StreamedResponse;
use League\Csv\Writer;

class CsvStoryExporter implements StoryExporterInterface
{
    public function __construct(
        private ModelTransformer $modelTransformer,
    ) {
    }

    public function export(Story $story): StreamedResponse
    {
        $storyId = $story['StoryId'] ?? 'unknown';
        $filename = "story-{$storyId}-" . now()->format('Ymd-His') . '.csv';

        // ToDo: Item CSV export? Maybe booth as zip file?
        return response()->streamDownload(
            callback: function () use ($story) {
                echo $this->formatStoryAsCsv($story);
            },
            name: $filename,
            headers: [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }

    private function formatStoryAsCsv(Story $story): string
    {
        $storyData = $this->modelTransformer->transformStory($story, []);

        // flatten with dot notation
        $flattenedData = Arr::dot($storyData);

        $csv = Writer::createFromString();

        // header row
        $csv->insertOne(array_keys($flattenedData));

        // data row
        $csv->insertOne(array_values($flattenedData));

        return $csv->toString();
    }
}
