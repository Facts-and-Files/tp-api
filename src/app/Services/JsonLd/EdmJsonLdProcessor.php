<?php

namespace App\Services\JsonLd;

use App\Services\JsonLd\NodeIndexer;
use App\Services\JsonLd\LiteralResolver;
use App\Services\JsonLd\FieldExtractor;
use App\Exceptions\InvalidManifestUrlException;

class EdmJsonLdProcessor
{
    public function __construct(
        private NodeIndexer $indexer,
        private LiteralResolver $resolver,
        private FieldExtractor $extractor
    ) {}

    public function processJsonLd(array $jsonLdData): array
    {
        $mappings = config('edm.mappings');
        $this->indexer->build($jsonLdData);

        return [
            'story'            => [],
            'recordId'         => '',
            'externalRecordId' => $this->extractor->resolveFieldValue('externalRecordId', $jsonLdData, $mappings) ?? '',
            'storyTitle'       => '',
            'manifestUrl'      => $this->extractManifestUri('manifestUrl', $jsonLdData, $mappings),
            'imageLink'        => '',
            'pdfImage'         => '',
            'imageLinks'       => '',
            'placeAdded'       => false,
        ];
    }

    private function extractManifestUri(string $name, array $jsonLdData, array $mappings): string
    {
        $url = $this->extractor->resolveFieldValue($name, $jsonLdData, $mappings) ?? $this->getManifestFromEuropeana();

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidManifestUrlException("Invalid Manifest URL: {$url}");
        }

        return $url;
    }

    private function getManifestFromEuropeana(): ?string
    {
        // try to get the mainfest via POST request directly from Europeana
        // ask Marcin if this is still a valid process or not neccessary
print_r('try to get the mainfest via POST request directly from Europeana');
        return null;
    }
}
