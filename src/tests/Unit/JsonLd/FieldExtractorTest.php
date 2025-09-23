<?php

namespace Tests\Unit\JsonLd;

use App\Services\JsonLd\FieldExtractor;
use PHPUnit\Framework\TestCase;

class FieldExtractorTest extends TestCase
{
    public function test_extracts_value_from_configured_property(): void
    {
        $extractor = new FieldExtractor();

        $data = [
            'iiif_url' => 'https://example.org/manifest.json',
        ];

        $mappings = [
            'manifestUrl' => [
                'paths' => [
                    ['path' => 'iiif_url', 'type' => null],
                ]
            ]
        ];

        $result = $extractor->resolveFieldValue('manifestUrl', $data, $mappings);

        $this->assertSame('https://example.org/manifest.json', $result);
    }

    public function test_extracts_value_from_configured_path(): void
    {
        $extractor = new FieldExtractor();

        $data = [
            '@graph' => [
                [
                    '@id' => 'http://example.org/123',
                    '@type' => "edm:WebResource",
                    'dcterms:isReferencedBy' => [
                        '@id' => 'https://example.org/manifest.json',
                    ],
                ],
            ],
        ];

        $mappings = [
            'manifestUrl' => [
                'paths' => [
                    ['path' => 'dcterms:isReferencedBy.@id', 'type' => 'edm:WebResource'],
                ],
            ],
        ];

        $result = $extractor->resolveFieldValue('manifestUrl', $data, $mappings);

        $this->assertSame('https://example.org/manifest.json', $result);
    }

}
