<?php

namespace App\Services\JsonLd;

use App\Exceptions\InvalidManifestUrlException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class EdmJsonLdProcessor
{
    public function __construct(
        private NodeIndexer $indexer,
        private LiteralResolver $resolver,
        private FieldExtractor $extractor,
    ) {}

    public function processJsonLd(array $jsonLdData): array
    {
        $mappings = config('edm.mappings');

        // build node index for reference resolution
        $this->indexer->build($jsonLdData);
        $nodeIndex = $this->indexer->all();

        return [
            'story'            => [],
            'recordId'         => '',
            'externalRecordId' => $this->extractor->resolveFieldValue('externalRecordId', $jsonLdData, $mappings, $nodeIndex) ?? '',
            'storyTitle'       => '',
            'manifestUrl'      => $this->extractManifestUri('manifestUrl', $jsonLdData, $mappings, $nodeIndex),
            'imageLink'        => '',
            'pdfImage'         => '',
            'imageLinks'       => '',
            'placeAdded'       => false,
        ];
    }

    private function extractManifestUri(
        string $name,
        array $jsonLdData,
        array $mappings,
        array $nodeIndex,
    ): string {
        $url = $this->extractor->resolveFieldValue($name, $jsonLdData, $mappings, $nodeIndex);

        if (!$url) {
            return '';
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidManifestUrlException("Invalid Manifest URL: {$url}");
        }

        $this->assertUrlReachable($url);

        return $url;
    }

    private function assertUrlReachable(string $url, array $options = []): void
    {
        try {
            $request = Http::timeout($options['timeout'] ?? 5);

            if (!empty($options['headers'])) {
                $request->withHeaders($options['headers']);
            }

            $response = $request->head($url);

            if (!$response->successful()) {
                throw new InvalidManifestUrlException("Manifest URL not reachable: {$url}");
            }
        } catch (RequestException $e) {
            throw new InvalidManifestUrlException("Error reaching Manifest URL: {$url}");
        }
    }
}
