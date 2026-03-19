<?php

namespace App\Services\Export;

use App\Models\Item;
use App\Models\Story;
use App\Services\Converter\YamlConverter;
use App\Traits\BuildExportFilename;
use Symfony\Component\Yaml\Yaml;

class YamlStoryExporter implements StoryExporterInterface
{
    use BuildExportFilename;

    public function __construct(
        private YamlConverter $yamlConverter,
    ) {}

    public function export(Story $story): string
    {
        $items = Item::whereIn('ItemId', $story->ItemIds)->orderBy('OrderIndex')->get();

        $data = [
            ...$this->yamlConverter->convertStory($story, ['ItemIds']),
            ...$this->yamlConverter->convertItems($items),
        ];

        $yaml = Yaml::dump(
            $data,
            5,
            2,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
            // enable with newer version ^7.4
            /* Yaml::DUMP_NULL_AS_EMPTY, */
            /* Yaml::DUMP_COMPACT_NESTED_MAPPING, */
        );

        return $yaml;
    }
}
