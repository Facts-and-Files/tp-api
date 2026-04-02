<?php

namespace App\Services\Export;

use App\Models\Story;
use Illuminate\Validation\ValidationException;
use ZipStream\ZipStream;

class StoryExportManager
{
    public function __construct(
        private CsvStoryExporter $csvStoryExporter,
        private YamlStoryExporter $yamlStoryExporter,
        private MetsStoryExporter $metsStoryExporter,
    ) {}

    public function export(Story $story, string $format): mixed
    {
        return match ($format) {
            'yml' => $this->yamlStoryExporter->export($story),
            'mets' => $this->metsStoryExporter->export($story),
            default => throw ValidationException::withMessages(
                ["Export format {$format} is not supported."],
            ),
        };
    }

    public function exportZip(Story $story): ZipStream
    {
        return $this->csvStoryExporter->export($story);
    }
}
