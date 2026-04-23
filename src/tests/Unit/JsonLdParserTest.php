<?php

namespace Tests\Unit;

use App\Services\Import\JsonLdParser;
use Tests\TestCase;

class JsonLdParserTest extends TestCase
{
    private JsonLdParser $parser;

    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new JsonLdParser();
    }

    // ── RecordId / ExternalRecordId ────────────────────────────────────────

    public function test_extracts_record_id_and_external_record_id_from_provided_cho(): void
    {
        $graph = [
            [
                '@type' => 'edm:ProvidedCHO',
                '@id'   => 'http://data.europeana.eu/item/123/abc',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('http://data.europeana.eu/item/123/abc', $result['externalRecordId']);
        $this->assertSame('/123/abc', $result['recordId']);
    }

    // ── Scalar metadata fields ─────────────────────────────────────────────

    public function test_extracts_scalar_dc_title(): void
    {
        $graph = [
            [
                '@type'    => 'edm:ProvidedCHO',
                'dc:title' => 'My Title',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('My Title', $result['fields']['dc:title']);
    }

    public function test_extracts_dc_title_from_json_ld_value_object(): void
    {
        $graph = [
            [
                '@type'    => 'edm:ProvidedCHO',
                'dc:title' => ['@value' => 'Value Object Title'],
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('Value Object Title', $result['fields']['dc:title']);
    }

    public function test_extracts_dc_title_from_json_ld_id_object(): void
    {
        $graph = [
            [
                '@type'    => 'edm:ProvidedCHO',
                'dc:title' => ['@id' => 'http://example.com/title'],
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('http://example.com/title', $result['fields']['dc:title']);
    }

    // ── Multi-value concatenation ──────────────────────────────────────────

    public function test_concatenates_duplicate_fields_with_double_pipe(): void
    {
        $graph = [
            [
                '@type'    => 'edm:ProvidedCHO',
                'dc:title' => ['@value' => 'First'],
            ],
            [
                'dc:title' => ['@value' => 'Second'],
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('First || Second', $result['fields']['dc:title']);
    }

    public function test_prefers_english_value_from_language_tagged_array(): void
    {
        $graph = [
            [
                'dc:title' => [
                    ['@language' => 'de', '@value' => 'Deutscher Titel'],
                    ['@language' => 'en', '@value' => 'English Title'],
                ],
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('English Title', $result['fields']['dc:title']);
    }

    // ── Description sanitisation ───────────────────────────────────────────

    public function test_strips_special_chars_from_dc_description(): void
    {
        $graph = [
            [
                'dc:description' => '{"Some [description]"}',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertStringNotContainsString('"', $result['fields']['dc:description']);
        $this->assertStringNotContainsString('{', $result['fields']['dc:description']);
        $this->assertStringNotContainsString('[', $result['fields']['dc:description']);
        $this->assertSame('Some description', $result['fields']['dc:description']);
    }

    // ── Place extraction ───────────────────────────────────────────────────

    public function test_extracts_place_lat_lon_from_geo_namespace(): void
    {
        $graph = [
            [
                '@type'    => 'edm:Place',
                'geo:lat'  => '52.5200',
                'geo:long' => '13.4050',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('52.5200', $result['fields']['PlaceLatitude']);
        $this->assertSame('13.4050', $result['fields']['PlaceLongitude']);
    }

    public function test_extracts_place_lat_lon_from_wgs84_namespace(): void
    {
        $graph = [
            [
                '@type'         => 'edm:Place',
                'wgs84_pos:lat'  => '48.8566',
                'wgs84_pos:long' => '2.3522',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('48.8566', $result['fields']['PlaceLatitude']);
        $this->assertSame('2.3522', $result['fields']['PlaceLongitude']);
    }

    public function test_extracts_place_name_from_scalar_skos_pref_label(): void
    {
        $graph = [
            [
                '@type'           => 'edm:Place',
                'skos:prefLabel'  => 'Berlin',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('Berlin', $result['fields']['PlaceName']);
    }

    public function test_only_first_place_node_is_used(): void
    {
        $graph = [
            [
                '@type'    => 'edm:Place',
                'geo:lat'  => '52.00',
                'geo:long' => '13.00',
            ],
            [
                '@type'    => 'edm:Place',
                'geo:lat'  => '48.00',
                'geo:long' => '2.00',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('52.00', $result['fields']['PlaceLatitude']);
    }

    // ── edm:WebResource / image links ─────────────────────────────────────

    public function test_collects_image_links_from_web_resource_nodes(): void
    {
        $graph = [
            [
                '@type' => 'edm:WebResource',
                '@id'   => 'https://example.com/image1.jpg',
            ],
            [
                '@type' => 'edm:WebResource',
                '@id'   => 'https://example.com/image2.jpg',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertCount(2, $result['imageLinks']);
        $this->assertContains('https://example.com/image1.jpg', $result['imageLinks']);
    }

    public function test_separates_pdf_image_from_regular_image_links(): void
    {
        $graph = [
            [
                '@type'                => 'edm:WebResource',
                '@id'                  => 'https://example.com/doc.pdf',
                'ebucore:hasMimeType'  => 'application/pdf',
            ],
            [
                '@type' => 'edm:WebResource',
                '@id'   => 'https://example.com/image.jpg',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('https://example.com/doc.pdf', $result['pdfImage']);
        $this->assertCount(1, $result['imageLinks']);
        $this->assertNotContains('https://example.com/doc.pdf', $result['imageLinks']);
    }

    // ── Manifest URL resolution ────────────────────────────────────────────

    public function test_top_level_iiif_url_is_used_as_manifest_url(): void
    {
        $result = $this->parser->parse([], 'https://example.com/manifest.json');

        $this->assertSame('https://example.com/manifest.json', $result['manifestUrl']);
    }

    public function test_dcterms_is_referenced_by_used_as_manifest_url_when_no_top_level_url(): void
    {
        $graph = [
            [
                '@type'                   => 'edm:WebResource',
                '@id'                     => 'https://example.com/image.jpg',
                'dcterms:isReferencedBy'  => ['@id' => 'https://example.com/iiif/manifest'],
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertSame('https://example.com/iiif/manifest', $result['manifestUrl']);
    }

    public function test_top_level_iiif_url_takes_priority_over_dcterms_reference(): void
    {
        $graph = [
            [
                '@type'                   => 'edm:WebResource',
                '@id'                     => 'https://example.com/image.jpg',
                'dcterms:isReferencedBy'  => ['@id' => 'https://example.com/iiif/from-node'],
            ],
        ];

        $result = $this->parser->parse($graph, 'https://example.com/iiif/top-level');

        $this->assertSame('https://example.com/iiif/top-level', $result['manifestUrl']);
    }

    // ── Empty / minimal payloads ───────────────────────────────────────────

    public function test_returns_empty_strings_for_missing_record_identifiers(): void
    {
        $result = $this->parser->parse([], null);

        $this->assertSame('', $result['externalRecordId']);
        $this->assertSame('', $result['recordId']);
    }

    public function test_unknown_nodes_are_ignored_without_errors(): void
    {
        $graph = [
            [
                '@type'    => 'skos:Concept',
                'skos:prefLabel' => 'Something irrelevant',
            ],
        ];

        $result = $this->parser->parse($graph, null);

        $this->assertEmpty($result['fields']);
    }
}
