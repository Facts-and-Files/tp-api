<?php

namespace App\Services\Export;

use App\Models\Story;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoryExportManager
{
    public function __construct(
        private YamlStoryExporter $yamlStoryExporter,
        private CsvStoryExporter $csvStoryExporter,
    ) {
    }

    public function export(Story $story, string $format): StreamedResponse
    {
        return match ($format) {
            'yml' => $this->yamlStoryExporter->export($story),
            'csv' => $this->csvStoryExporter->export($story),
            default => throw ValidationException::withMessages(
                ["Export format {$format} is not supported."]
            ),
        };
    }
}
