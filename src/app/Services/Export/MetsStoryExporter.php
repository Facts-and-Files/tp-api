<?php

namespace App\Services\Export;

use App\Models\Story;
use App\Services\Converter\MetsConverter;

class MetsStoryExporter implements StoryExporterInterface
{
    public function __construct(
        private MetsStoryDataBuilder $dataBuilder,
        private MetsConverter $metsConverter,
    ) {}

    public function export(Story $story): string
    {
        $preparedData = $this->dataBuilder->build($story);
        $mets = $this->metsConverter->convert($preparedData);

        return $mets->saveXML();
    }
}
