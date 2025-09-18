<?php

namespace Tests\Unit;

use App\Services\EdmJsonLdProcessorService;
use App\EDM\Literal;
use Tests\TestCase;

class EdmJsonLdProcessorServiceTest extends TestCase
{
    private EdmJsonLdProcessorService $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new EdmJsonLdProcessorService();
    }

    protected function loadFixture(string $filename, ?string $key = null): mixed
    {
        $array = json_decode(file_get_contents(__DIR__."/fixtures/{$filename}.json"), true);
        return $key ? $array[$key] : $array;
    }

    public function test_create_entity_index_with_graph(): void
    {
        $edmObject = $this->loadFixture('min_object_refrence_mixed_with_graph');

        $this->processor->processJsonLd($edmObject);
        $index = $this->processor->getNodeIndexKeys();

        $this->assertEquals($index[0], $edmObject['@id']);
        $this->assertEquals($index[1], $edmObject['edm:hasView']['@id']);
        $this->assertEquals($index[2], $edmObject['@graph'][0]['@id']);
        $this->assertEquals($index[3], $edmObject['@graph'][1]['@id']);

        $this->assertEquals(count($index), 4);
    }

    public function test_resolve_id_references(): void
    {
        $edmObject = $this->loadFixture('min_object_refrence_mixed_with_graph');

        $this->processor->processJsonLd($edmObject);
        $entity_1 = $this->processor->resolveEntity($edmObject['edm:related']['@id']);
        $entity_2 = $this->processor->resolveEntity($edmObject['@graph'][0]['edm:hasView']['@id']);
        $entity_3 = $this->processor->resolveEntity($edmObject['@graph'][0]['edm:related']['@id']);

        $this->assertEquals($entity_1, $edmObject['@graph'][0]);
        $this->assertEquals($entity_2, $edmObject['@graph'][1]);
        $this->assertEquals($entity_3, $edmObject);
    }

    public function test_get_literals_direct_literal(): void
    {
        $edmObject = $this->loadFixture('min_object_refrence_mixed_with_graph');

        $this->processor->processJsonLd($edmObject);
        $literals = $this->processor->getLiterals('http://example.org/record/graph1', 'dc:title');

        $this->assertCount(1, $literals);
        $this->assertInstanceOf(Literal::class, $literals[0]);
        $this->assertSame('Graph-Level Object', $literals[0]->value);
    }

    public function test_get_literals_reference(): void
    {
        $edmObject = $this->loadFixture('edm_complete');

        $this->processor->processJsonLd($edmObject);
        $literals = $this->processor->getLiterals('http://example.org/proxy/provider/12345', 'dcterms:creator');

        $this->assertCount(1, $literals);
        $this->assertSame('Rembrandt van Rijn', $literals[0]->value);

    }

    public function test_get_literals_language_tagged_literal(): void
    {
        $edmObject = $this->loadFixture('edm_complete');

        $this->processor->processJsonLd($edmObject);
        $literals = $this->processor->getLiterals('http://example.org/proxy/provider/67890', 'dc:title');

        $this->assertCount(1, $literals);
        $this->assertSame('Child Record: A Detail Shot', $literals[0]->value);
        $this->assertSame('en', $literals[0]->language);
    }

    public function test_resolve_property_across_graph(): void
    {
        $edmObject = $this->loadFixture('edm_complete');

        $this->processor->processJsonLd($edmObject);
        $titles = $this->processor->resolveProperty('dc:title');

        $this->assertCount(2, $titles);

        $names = array_map(fn(Literal $lit) => $lit->value, $titles);
        $this->assertContains('Parent Record: A Painting', $names);
        $this->assertContains('Child Record: A Detail Shot', $names);
    }

    public function test_get_literals_missing_property(): void
    {
        $edmObject = $this->loadFixture('edm_complete');

        $this->processor->processJsonLd($edmObject);
        $literals = $this->processor->getLiterals('http://example.org/item/1', 'dc:publisher');

        $this->assertSame([], $literals);
    }

    public function test_resolve_property_missing_property(): void
    {
        $edmObject = $this->loadFixture('edm_complete');

        $this->processor->processJsonLd($edmObject);
        $literals = $this->processor->resolveProperty('nonexistent:property');

        $this->assertSame([], $literals);
    }

    public function test_extract_converted_manifest_url(): void
    {
        $edmObject = [
            '@graph' => [],
            'iiif_url' => 'https:///iiif.example.com/manifest',
        ];

        $result = $this->processor->processJsonLd($edmObject);

        $this->assertSame($result['manifestUrl'], $edmObject['iiif_url']);
    }

    public function test_extract_original_manifest_url(): void
    {
        $edmObject = [
            '@graph' => [
                [
                    '@id' => 'http://example.org/123',
                    '@type' => "edm:WebResource",
                    'dcterms:isReferencedBy' => [
                        '@id' => 'http://example.com/manifest/123',
                    ],
                ],
            ],
        ];

        $result = $this->processor->processJsonLd($edmObject);

        $this->assertSame($result['manifestUrl'], $edmObject['@graph'][0]['dcterms:isReferencedBy']['@id']);
    }


    // /** @test */
    // public function it_processes_place_data_with_coordinates(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => '#place_1',
    //             '@type' => 'edm:Place',
    //             'geo:lat' => 52.5200,
    //             'geo:long' => 13.4050,
    //             'skos:prefLabel' => 'Berlin'
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertEquals(52.5200, $result['story']['PlaceLatitude']);
    //     $this->assertEquals(13.4050, $result['story']['PlaceLongitude']);
    //     $this->assertEquals('Berlin', $result['story']['PlaceName']);
    //     $this->assertTrue($result['placeAdded']);
    // }
    //
    // /** @test */
    // public function it_processes_place_with_wgs84_coordinates(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => '#place_1',
    //             '@type' => 'edm:Place',
    //             'wgs84_pos:lat' => 48.8566,
    //             'wgs84_pos:long' => 2.3522,
    //             'skos:prefLabel' => 'Paris'
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertEquals(48.8566, $result['story']['PlaceLatitude']);
    //     $this->assertEquals(2.3522, $result['story']['PlaceLongitude']);
    //     $this->assertEquals('Paris', $result['story']['PlaceName']);
    // }
    //
    // /** @test */
    // public function it_processes_agent_data(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => '#agent_1',
    //             '@type' => 'edm:Agent',
    //             'skos:prefLabel' => 'Leonardo da Vinci'
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertStringContains('Leonardo da Vinci', $result['story']['edm:agent']);
    //     $this->assertStringContains('#agent_1', $result['story']['edm:agent']);
    // }
    //
    // /** @test */
    // public function it_processes_web_resource_with_manifest(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => 'http://example.com/image/1',
    //             '@type' => 'edm:WebResource',
    //             'dcterms:isReferencedBy' => [
    //                 '@id' => 'http://example.com/manifest.json'
    //             ]
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertEquals('http://example.com/manifest.json', $result['manifestUrl']);
    // }
    //
    // /** @test */
    // public function it_processes_web_resource_with_pdf(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => 'http://example.com/document.pdf',
    //             '@type' => 'edm:WebResource',
    //             'ebucore:hasMimeType' => 'application/pdf'
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertEquals('http://example.com/document.pdf', $result['pdfImage']);
    // }
    //
    // /** @test */
    // public function it_processes_web_resource_with_image(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => 'http://example.com/image.jpg',
    //             '@type' => 'edm:WebResource',
    //             'ebucore:hasMimeType' => 'image/jpeg'
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertContains('http://example.com/image.jpg', $result['imageLinks']);
    // }
    //
    // /** @test */
    // public function it_handles_array_values(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => 'http://example.com/item/1',
    //             '@type' => 'edm:ProvidedCHO',
    //             'dc:subject' => ['Art', 'History', 'Culture']
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertStringContains('Art', $result['story']['dc:subject']);
    //     $this->assertStringContains('History', $result['story']['dc:subject']);
    //     $this->assertStringContains('Culture', $result['story']['dc:subject']);
    //     $this->assertStringContains(' || ', $result['story']['dc:subject']);
    // }
    //
    // /** @test */
    // public function it_handles_object_values_with_language(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => 'http://example.com/item/1',
    //             '@type' => 'edm:ProvidedCHO',
    //             'dc:title' => [
    //                 ['@value' => 'English Title', '@language' => 'en'],
    //                 ['@value' => 'Titre Français', '@language' => 'fr']
    //             ]
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertStringContains('English Title', $result['story']['dc:title']);
    //     $this->assertStringContains('Titre Français', $result['story']['dc:title']);
    // }
    //
    // /** @test */
    // public function it_merges_duplicate_fields(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => 'http://example.com/item/1',
    //             '@type' => 'edm:ProvidedCHO',
    //             'dc:creator' => 'First Creator'
    //         ],
    //         [
    //             '@id' => 'http://example.com/item/1',
    //             'dc:creator' => 'Second Creator'
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertStringContains('First Creator', $result['story']['dc:creator']);
    //     $this->assertStringContains('Second Creator', $result['story']['dc:creator']);
    //     $this->assertStringContains(' || ', $result['story']['dc:creator']);
    // }
    //
    // /** @test */
    // public function it_handles_iiif_url_field(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => 'http://example.com/item/1',
    //             '@type' => 'edm:ProvidedCHO',
    //             'iiif_url' => 'http://example.com/iiif/manifest.json'
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertEquals('http://example.com/iiif/manifest.json', $result['manifestUrl']);
    // }

    // /** @test */
    // public function it_handles_invalid_manifest_urls(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => 'http://example.com/image/1',
    //             '@type' => 'edm:WebResource',
    //             'dcterms:isReferencedBy' => [
    //                 '@id' => 'not-a-valid-url'
    //             ]
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertEquals('', $result['manifestUrl']);
    // }
    //
    // /** @test */
    // public function it_extracts_record_id_from_complex_urls(): void
    // {
    //     $jsonLdData = [
    //         [
    //             '@id' => 'http://data.europeana.eu/item/123/abc_def',
    //             '@type' => 'edm:ProvidedCHO'
    //         ]
    //     ];
    //
    //     $result = $this->processor->processJsonLd($jsonLdData);
    //
    //     $this->assertEquals('http://data.europeana.eu/item/123/abc_def', $result['externalRecordId']);
    //     $this->assertEquals('/123/abc_def', $result['recordId']);
    // }
}
