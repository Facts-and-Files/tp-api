<?php

namespace App\Services\Import;

use App\Services\Import\DTO\ParsedJsonLdData;
use App\Services\Import\EDM\EdmNodeExtractor;
use App\Services\Import\Support\JsonLdGraphIndexer;
use App\Services\Import\Support\JsonLdGraphNormalizer;

final class JsonLdParser
{
    public function __construct(
        private readonly JsonLdGraphIndexer $indexer = new JsonLdGraphIndexer(),
        private readonly JsonLdGraphNormalizer $normalizer = new JsonLdGraphNormalizer(),
        private readonly EdmNodeExtractor $extractor = new EdmNodeExtractor(),
    ) {
    }

    public function parse(array $graph, ?string $iiifUrl = null): ParsedJsonLdData
    {
        $index = $this->indexer->build($graph);
        $normalizedGraph = $this->normalizer->normalizeGraph($graph, $index);

        return $this->extractor->extract($normalizedGraph, $iiifUrl);
    }
}
