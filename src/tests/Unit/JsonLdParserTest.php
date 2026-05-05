<?php

namespace Tests\Unit;

use App\Services\Import\DTO\ParsedJsonLdData;
use App\Services\Import\JsonLdParser;
use Tests\TestCase;

final class JsonLdParserTest extends TestCase
{
    private JsonLdParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new JsonLdParser();
    }

    public function test_extracts_record_id_and_external_record_id_from_provided_cho(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                '@id' => 'http://data.europeana.eu/item/123/abc',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertInstanceOf(ParsedJsonLdData::class, $result);
        $this->assertSame('http://data.europeana.eu/item/123/abc', $result->externalRecordId);
        $this->assertSame('/123/abc', $result->recordId);
    }

    public function test_extracts_scalar_dc_title(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => 'My Title',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('My Title', $result->fields['dc:title']);
    }

    public function test_extracts_dc_title_from_json_ld_value_object(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => ['@value' => 'Value Object Title'],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('Value Object Title', $result->fields['dc:title']);
    }

    public function test_extracts_dc_title_from_json_ld_id_object(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => ['@id' => 'http://example.com/title'],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('http://example.com/title', $result->fields['dc:title']);
    }

    public function test_flattens_distinct_dc_title_values_with_double_pipe(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => ['@value' => 'First'],
            ],
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => ['@value' => 'Second'],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('First || Second', $result->fields['dc:title']);
    }

    public function test_deduplicates_duplicate_dc_title_values_across_nodes(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => ['@value' => 'Same Title'],
            ],
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => ['@value' => 'Same Title'],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('Same Title', $result->fields['dc:title']);
    }

    public function test_deduplicates_duplicate_dc_title_values_inside_array(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => [
                    ['@value' => 'Same Title'],
                    ['@value' => 'Same Title'],
                    ['@value' => 'Other Title'],
                ],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('Same Title || Other Title', $result->fields['dc:title']);
    }

    public function test_prefers_english_value_for_scalar_place_name(): void
    {
        $graph = [
            [
                '@type' => 'edm:Place',
                'geo:lat' => '55.6050',
                'geo:long' => '13.0038',
                'skos:prefLabel' => [
                    ['@language' => 'de', '@value' => 'Malmö auf Deutsch'],
                    ['@language' => 'en', '@value' => 'Malmo'],
                ],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('Malmo', $result->fields['PlaceName']);
    }

    public function test_flattens_distinct_language_values_for_story_fields(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => [
                    ['@language' => 'de', '@value' => 'Deutscher Titel'],
                    ['@language' => 'en', '@value' => 'English Title'],
                ],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('Deutscher Titel || English Title', $result->fields['dc:title']);
    }

    public function test_strips_special_chars_from_dc_description(): void
    {
        $graph = [
            [
                'dc:description' => '{"Some [description]"}',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertStringNotContainsString('"', $result->fields['dc:description']);
        $this->assertStringNotContainsString('{', $result->fields['dc:description']);
        $this->assertStringNotContainsString('[', $result->fields['dc:description']);
        $this->assertSame('Some description', $result->fields['dc:description']);
    }

    public function test_extracts_place_lat_lon_from_geo_namespace(): void
    {
        $graph = [
            [
                '@type' => 'edm:Place',
                'geo:lat' => '52.5200',
                'geo:long' => '13.4050',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('52.5200', $result->fields['PlaceLatitude']);
        $this->assertSame('13.4050', $result->fields['PlaceLongitude']);
    }

    public function test_extracts_place_lat_lon_from_wgs84_namespace(): void
    {
        $graph = [
            [
                '@type' => 'edm:Place',
                'wgs84_pos:lat' => '48.8566',
                'wgs84_pos:long' => '2.3522',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('48.8566', $result->fields['PlaceLatitude']);
        $this->assertSame('2.3522', $result->fields['PlaceLongitude']);
    }

    public function test_extracts_place_name_from_scalar_skos_pref_label(): void
    {
        $graph = [
            [
                '@type' => 'edm:Place',
                'skos:prefLabel' => 'Berlin',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('Berlin', $result->fields['PlaceName']);
    }

    public function test_only_first_place_node_is_used(): void
    {
        $graph = [
            [
                '@type' => 'edm:Place',
                'geo:lat' => '52.00',
                'geo:long' => '13.00',
            ],
            [
                '@type' => 'edm:Place',
                'geo:lat' => '48.00',
                'geo:long' => '2.00',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('52.00', $result->fields['PlaceLatitude']);
        $this->assertSame('13.00', $result->fields['PlaceLongitude']);
    }

    public function test_deduplicates_agent_values(): void
    {
        $graph = [
            [
                '@type' => 'edm:Agent',
                '@id' => 'http://example.com/agent/1',
                'skos:prefLabel' => 'John Doe',
            ],
            [
                '@type' => 'edm:Agent',
                '@id' => 'http://example.com/agent/1',
                'skos:prefLabel' => 'John Doe',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('John Doe', $result->fields['edm:agent']);
    }

    public function test_flattens_distinct_agent_values(): void
    {
        $graph = [
            [
                '@type' => 'edm:Agent',
                '@id' => 'http://example.com/agent/1',
                'skos:prefLabel' => 'John Doe',
            ],
            [
                '@type' => 'edm:Agent',
                '@id' => 'http://example.com/agent/2',
                'skos:prefLabel' => 'Jane Doe',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame(
            'John Doe || Jane Doe',
            $result->fields['edm:agent']
        );
    }

    public function test_extracts_pdf_image_from_web_resource(): void
    {
        $graph = [
            [
                '@type' => 'edm:WebResource',
                '@id' => 'https://example.com/doc.pdf',
                'ebucore:hasMimeType' => 'application/pdf',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('https://example.com/doc.pdf', $result->pdfImage);
    }

    public function test_non_pdf_web_resource_does_not_set_pdf_image(): void
    {
        $graph = [
            [
                '@type' => 'edm:WebResource',
                '@id' => 'https://example.com/image.jpg',
                'ebucore:hasMimeType' => 'image/jpeg',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('', $result->pdfImage);
    }

    public function test_top_level_iiif_url_is_used_as_manifest_url(): void
    {
        $result = $this->parser->parse([], 'https://example.com/manifest.json');

        $this->assertSame('https://example.com/manifest.json', $result->manifestUrl);
        $this->assertSame('token', $result->manifestAuthMode);
    }

    public function test_dcterms_is_referenced_by_used_as_manifest_url_when_no_top_level_url(): void
    {
        $graph = [
            [
                '@type' => 'edm:WebResource',
                '@id' => 'https://example.com/image.jpg',
                'dcterms:isReferencedBy' => ['@id' => 'https://example.com/iiif/manifest'],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('https://example.com/iiif/manifest', $result->manifestUrl);
        $this->assertSame('public', $result->manifestAuthMode);
    }

    public function test_top_level_iiif_url_takes_priority_over_dcterms_reference(): void
    {
        $graph = [
            [
                '@type' => 'edm:WebResource',
                '@id' => 'https://example.com/image.jpg',
                'dcterms:isReferencedBy' => ['@id' => 'https://example.com/iiif/from-node'],
            ],
        ];

        $result = $this->parser->parse($graph, 'https://example.com/iiif/top-level');

        $this->assertSame('https://example.com/iiif/top-level', $result->manifestUrl);
        $this->assertSame('token', $result->manifestAuthMode);
    }

    public function test_resolves_nested_same_document_dc_title_reference(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => ['@id' => '#title-node'],
                'edm:hasView' => [
                    '@id' => '#wrapper-node',
                    'nested' => [
                        '@id' => '#title-node',
                        '@value' => 'Nested Resolved Title',
                    ],
                ],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('Nested Resolved Title', $result->fields['dc:title']);
    }

    public function test_resolves_nested_same_document_place_label_reference(): void
    {
        $graph = [
            [
                '@type' => 'edm:Place',
                'geo:lat' => '55.6050',
                'geo:long' => '13.0038',
                'skos:prefLabel' => ['@id' => '#place-label'],
                'extra' => [
                    '@id' => '#wrapper',
                    'labelNode' => [
                        '@id' => '#place-label',
                        '@value' => 'Malmö',
                    ],
                ],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('Malmö', $result->fields['PlaceName']);
    }

    public function test_resolves_nested_same_document_manifest_reference(): void
    {
        $graph = [
            [
                '@type' => 'edm:WebResource',
                '@id' => 'https://example.com/image.jpg',
                'dcterms:isReferencedBy' => ['@id' => '#manifest-node'],
                'edm:hasView' => [
                    '@id' => '#container',
                    'nested' => [
                        '@id' => '#manifest-node',
                        'rdf:value' => 'https://example.com/iiif/manifest',
                    ],
                ],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('https://example.com/iiif/manifest', $result->manifestUrl);
        $this->assertSame('public', $result->manifestAuthMode);
    }

    public function test_circular_same_document_reference_falls_back_without_infinite_loop(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => ['@id' => '#a'],
            ],
            [
                '@id' => '#a',
                'related' => ['@id' => '#b'],
            ],
            [
                '@id' => '#b',
                'related' => ['@id' => '#a'],
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame('#a', $result->fields['dc:title']);
    }

    public function test_returns_empty_strings_for_missing_record_identifiers(): void
    {
        $result = $this->parser->parse([]);

        $this->assertSame('', $result->externalRecordId);
        $this->assertSame('', $result->recordId);
    }

    public function test_unknown_nodes_are_ignored_without_errors(): void
    {
        $graph = [
            [
                '@type' => 'skos:Concept',
                'skos:prefLabel' => 'Something irrelevant',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertEmpty($result->fields);
    }

    public function test_preserves_commas_in_literal_values(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                'dc:title' => 'Letters, Diaries, and Notes',
            ],
        ];

        $result = $this->parser->parse($graph);

        $this->assertSame(
            'Letters, Diaries, and Notes',
            $result->fields['dc:title']
        );
    }

    public function test_resolves_id_references_to_labels_for_any_field(): void
    {
        $graph = [
            [
                '@id' => 'file:///home/vcap/app/#agentOf:nnm1wZl_1',
                '@type' => 'edm:Agent',
                'skos:prefLabel' => [
                    '@language' => 'pl',
                    '@value' => 'Hauke, Maurycy (1773-1830)',
                ],
            ],
            [
                '@id' => 'http://data.europeana.eu/item/456/_nnm1wZl',
                '@type' => 'edm:ProvidedCHO',
                'dc:creator' => [
                    '@id' => 'file:///home/vcap/app/#agentOf:nnm1wZl_1',
                ],
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame(
            'Hauke, Maurycy (1773-1830)',
            $result->fields['dc:creator'] ?? null
        );
    }
}
