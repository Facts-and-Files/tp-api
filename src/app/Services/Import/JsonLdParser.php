<?php

namespace App\Services\Import;

use App\Services\Import\DTO\ParsedJsonLdData;
use App\Services\Import\EDM\EdmNodeExtractor;
use App\Services\Import\Support\JsonLdGraphIndexer;
use App\Services\Import\Support\JsonLdGraphNormalizer;
use App\Services\Import\Support\JsonLdValueExtractor;

final class JsonLdParser
{
    private readonly JsonLdGraphIndexer $indexer;
    private readonly JsonLdGraphNormalizer $normalizer;
    private readonly EdmNodeExtractor $extractor;

    public function __construct(
        ?JsonLdGraphIndexer $indexer = null,
        ?JsonLdGraphNormalizer $normalizer = null,
        ?EdmNodeExtractor $extractor = null,
    ) {
        $this->indexer = $indexer ?? new JsonLdGraphIndexer();
        $this->normalizer = $normalizer ?? new JsonLdGraphNormalizer();
        $this->extractor = $extractor ?? new EdmNodeExtractor(new JsonLdValueExtractor());
    }

    public function parse(array $graph, ?string $iiifUrl = null): ParsedJsonLdData
    {
        $index = $this->indexer->build($graph);
        $normalizedGraph = $this->normalizer->normalizeGraph($graph, $index);

        return $this->extractor->extract($normalizedGraph, $iiifUrl);
    }
}
