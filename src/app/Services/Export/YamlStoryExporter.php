<?php

namespace App\Services\Export;

use App\Models\Story;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Yaml\Yaml;

class YamlStoryExporter implements StoryExporterInterface
{
    public function __construct(
        private YamlTransformer $yamlTransformer,
    ) {
    }

    public function export(Story $story): StreamedResponse
    {
        $storyId = $story['StoryId'] ?? 'unknown';
        $filename = "story-{$storyId}-" . now()->format('Ymd-His') . '.yml';

        return response()->streamDownload(
            callback: function () use ($story) {
                echo $this->formatStoryAsYaml($story);
            },
            name: $filename,
            headers: [
                'Content-Type' => 'application/yaml; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }

    private function formatStoryAsYaml(Story $story): string
    {
        $data = [
            ...$this->yamlTransformer->transformStory($story, ['ItemIds']),
            ...$this->yamlTransformer->transformItems($story->ItemIds),
        ];
        $yaml = Yaml::dump(
            $data, 5, 2,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
            // enable with newer version ^7.4
            /* Yaml::DUMP_NULL_AS_EMPTY, */
            /* Yaml::DUMP_COMPACT_NESTED_MAPPING, */
        );

        return $yaml;
    }
}
