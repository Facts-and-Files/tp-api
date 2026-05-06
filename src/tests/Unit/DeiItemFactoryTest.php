<?php

namespace Tests\Unit;

use App\Services\Import\DeiItemFactory;
use App\Services\Import\IiifManifestClient;
use App\Services\Import\DTO\ParsedJsonLdData;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DeiItemFactoryTest extends TestCase
{
    // =========================================================================
    // fetchRequiredManifest
    // =========================================================================

    public function test_fetch_required_manifest_throws_when_manifest_url_is_empty(): void
    {
        $client  = Mockery::mock(IiifManifestClient::class);
        $factory = new DeiItemFactory($client);

        $parsed = new ParsedJsonLdData(
            fields: ['dc:title' => 'Title'],
            manifestUrl: '',
            pdfImage: '',
            externalRecordId: 'http://data.europeana.eu/item/1/test',
            recordId: '/1/test',
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IIIF manifest missing. Import aborted.');

        $factory->fetchRequiredManifest($parsed);
    }

    public function test_fetch_required_manifest_throws_when_manifest_has_no_canvases(): void
    {
        $client = Mockery::mock(IiifManifestClient::class);
        $client->shouldReceive('fetch')
            ->once()
            ->andReturn([
                'canvases'   => [],
                'imageLinks' => [],
            ]);

        $factory = new DeiItemFactory($client);

        $parsed = new ParsedJsonLdData(
            fields: ['dc:title' => 'Title'],
            manifestUrl: 'https://example.com/iiif/manifest',
            pdfImage: '',
            externalRecordId: 'http://data.europeana.eu/item/1/test',
            recordId: '/1/test',
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IIIF manifest contains no canvases. Import aborted.');

        $factory->fetchRequiredManifest($parsed);
    }

    // =========================================================================
    // makeFromManifest — v2 canvases
    // =========================================================================

    public function test_make_from_manifest_returns_preview_image_and_items(): void
    {
        $client  = Mockery::mock(IiifManifestClient::class);
        $factory = new DeiItemFactory($client);

        $parsed = new ParsedJsonLdData(
            fields: ['dc:title' => 'Manifest Story'],
            manifestUrl: 'https://example.com/iiif/manifest',
            pdfImage: '',
            externalRecordId: 'http://data.europeana.eu/item/2/test',
            recordId: '/2/test',
        );

        $manifest = [
            'canvases'   => [
                ['images' => [['resource' => ['@id' => 'https://example.com/p1.jpg']]]],
                ['images' => [['resource' => ['@id' => 'https://example.com/p2.jpg']]]],
            ],
            'imageLinks' => [
                'https://example.com/p1.jpg',
                'https://example.com/p2.jpg',
            ],
        ];

        $result = $factory->makeFromManifest($parsed, $manifest);

        $this->assertSame(
            ['@id' => 'https://example.com/p1.jpg'],
            $result['previewImage'],
        );

        $this->assertCount(2, $result['items']);

        $this->assertSame('Manifest Story Item 1', $result['items'][0]['Title']);
        $this->assertSame(
            json_encode(['@id' => 'https://example.com/p1.jpg']),
            $result['items'][0]['ImageLink'],
        );
        $this->assertSame(1, $result['items'][0]['OrderIndex']);
        $this->assertSame('https://example.com/iiif/manifest', $result['items'][0]['Manifest']);
        $this->assertSame('https://example.com/p1.jpg', $result['items'][0]['edm:WebResource']);

        $this->assertSame('Manifest Story Item 2', $result['items'][1]['Title']);
        $this->assertSame(
            json_encode(['@id' => 'https://example.com/p2.jpg']),
            $result['items'][1]['ImageLink'],
        );
        $this->assertSame(2, $result['items'][1]['OrderIndex']);
        $this->assertSame('https://example.com/iiif/manifest', $result['items'][1]['Manifest']);
        $this->assertSame('https://example.com/p2.jpg', $result['items'][1]['edm:WebResource']);
    }

    public function test_make_from_manifest_v2_stores_full_resource_object_as_image_link(): void
    {
        $client  = Mockery::mock(IiifManifestClient::class);
        $factory = new DeiItemFactory($client);

        $parsed = new ParsedJsonLdData(
            fields: ['dc:title' => 'Rich Resource'],
            manifestUrl: 'https://example.com/iiif/manifest',
            pdfImage: '',
            externalRecordId: 'http://data.europeana.eu/item/3/test',
            recordId: '/3/test',
        );

        $resource = [
            '@id'     => 'https://digi.example.com/image/p1.jpg',
            '@type'   => 'dctypes:Image',
            'format'  => 'image/jpeg',
            'height'  => 3185,
            'width'   => 1995,
            'service' => [
                '@context' => 'http://iiif.io/api/image/2/context.json',
                '@id'      => 'https://digi.example.com/iiif/2/p1.jpg',
                'profile'  => 'http://iiif.io/api/image/2/profiles/level2.json',
            ],
        ];

        $manifest = [
            'canvases'   => [
                ['images' => [['resource' => $resource]]],
            ],
            'imageLinks' => ['https://digi.example.com/image/p1.jpg'],
        ];

        $result = $factory->makeFromManifest($parsed, $manifest);

        // previewImage is the raw resource object
        $this->assertSame($resource, $result['previewImage']);

        // ImageLink is the JSON-encoded resource — @id key must be present
        $decoded = json_decode($result['items'][0]['ImageLink'], true);
        $this->assertSame('https://digi.example.com/image/p1.jpg', $decoded['@id']);
        $this->assertSame('dctypes:Image', $decoded['@type']);
        $this->assertArrayHasKey('service', $decoded);
    }

    // =========================================================================
    // makeFromManifest — v3 canvases (normalised to @id shape)
    // =========================================================================

    public function test_make_from_manifest_v3_normalises_image_link_to_at_id_shape(): void
    {
        $client  = Mockery::mock(IiifManifestClient::class);
        $factory = new DeiItemFactory($client);

        $parsed = new ParsedJsonLdData(
            fields: ['dc:title' => 'V3 Story'],
            manifestUrl: 'https://example.com/iiif/v3/manifest',
            pdfImage: '',
            externalRecordId: 'http://data.europeana.eu/item/4/test',
            recordId: '/4/test',
        );

        $manifest = [
            'canvases'   => [
                [
                    'id'    => 'https://example.com/canvas/1',
                    'type'  => 'Canvas',
                    'items' => [[
                        'items' => [[
                            'body' => [
                                'id'     => 'https://example.com/p1.jpg',
                                'type'   => 'Image',
                                'format' => 'image/jpeg',
                                'height' => 3185,
                                'width'  => 1995,
                                'service' => [[
                                    '@context' => 'http://iiif.io/api/image/2/context.json',
                                    '@id'      => 'https://example.com/iiif/2/p1.jpg',
                                    'profile'  => 'http://iiif.io/api/image/2/level2.json',
                                ]],
                            ],
                        ]],
                    ]],
                ],
            ],
            'imageLinks' => ['https://example.com/p1.jpg'],
        ];

        $result = $factory->makeFromManifest($parsed, $manifest);

        // previewImage must use @id (normalised), not id
        $this->assertArrayHasKey('@id', $result['previewImage']);
        $this->assertArrayNotHasKey('id', $result['previewImage']);
        $this->assertSame('https://example.com/p1.jpg', $result['previewImage']['@id']);

        // ImageLink must decode to an object with @id
        $decoded = json_decode($result['items'][0]['ImageLink'], true);
        $this->assertSame('https://example.com/p1.jpg', $decoded['@id']);
        $this->assertSame('dctypes:Image', $decoded['@type']);
        $this->assertSame('image/jpeg', $decoded['format']);
        $this->assertSame(3185, $decoded['height']);
        $this->assertSame(1995, $decoded['width']);

        // service is normalised from array to single object
        $this->assertIsArray($decoded['service']);
        $this->assertArrayHasKey('@context', $decoded['service']);
    }

    public function test_make_from_manifest_v3_normalises_specific_resource_body(): void
    {
        $client  = Mockery::mock(IiifManifestClient::class);
        $factory = new DeiItemFactory($client);

        $parsed = new ParsedJsonLdData(
            fields: ['dc:title' => 'V3 SpecificResource'],
            manifestUrl: 'https://example.com/iiif/v3/manifest',
            pdfImage: '',
            externalRecordId: 'http://data.europeana.eu/item/5/test',
            recordId: '/5/test',
        );

        $manifest = [
            'canvases'   => [[
                'items' => [[
                    'items' => [[
                        'body' => [
                            'type'   => 'SpecificResource',
                            'source' => ['id' => 'https://example.com/specific.jpg'],
                        ],
                    ]],
                ]],
            ]],
            'imageLinks' => ['https://example.com/specific.jpg'],
        ];

        $result = $factory->makeFromManifest($parsed, $manifest);

        $this->assertSame('https://example.com/specific.jpg', $result['previewImage']['@id']);

        $decoded = json_decode($result['items'][0]['ImageLink'], true);
        $this->assertSame('https://example.com/specific.jpg', $decoded['@id']);
    }

    public function test_make_from_manifest_v3_and_v2_produce_same_image_link_shape(): void
    {
        $client  = Mockery::mock(IiifManifestClient::class);
        $factory = new DeiItemFactory($client);

        $parsed = new ParsedJsonLdData(
            fields: ['dc:title' => 'Compat Test'],
            manifestUrl: 'https://example.com/iiif/manifest',
            pdfImage: '',
            externalRecordId: 'http://data.europeana.eu/item/6/test',
            recordId: '/6/test',
        );

        $v2Manifest = [
            'canvases'   => [[
                'images' => [['resource' => [
                    '@id'    => 'https://example.com/img.jpg',
                    '@type'  => 'dctypes:Image',
                    'format' => 'image/jpeg',
                    'height' => 100,
                    'width'  => 100,
                ]]],
            ]],
            'imageLinks' => ['https://example.com/img.jpg'],
        ];

        $v3Manifest = [
            'canvases'   => [[
                'items' => [[
                    'items' => [[
                        'body' => [
                            'id'     => 'https://example.com/img.jpg',
                            'type'   => 'Image',
                            'format' => 'image/jpeg',
                            'height' => 100,
                            'width'  => 100,
                        ],
                    ]],
                ]],
            ]],
            'imageLinks' => ['https://example.com/img.jpg'],
        ];

        $v2Result = $factory->makeFromManifest($parsed, $v2Manifest);
        $v3Result = $factory->makeFromManifest($parsed, $v3Manifest);

        $v2Decoded = json_decode($v2Result['items'][0]['ImageLink'], true);
        $v3Decoded = json_decode($v3Result['items'][0]['ImageLink'], true);

        // Both must expose @id at the same key
        $this->assertArrayHasKey('@id', $v2Decoded);
        $this->assertArrayHasKey('@id', $v3Decoded);
        $this->assertSame($v2Decoded['@id'], $v3Decoded['@id']);
        $this->assertSame($v2Decoded['@type'], $v3Decoded['@type']);
        $this->assertSame($v2Decoded['format'], $v3Decoded['format']);
    }

    // =========================================================================
    // makeFromManifest — edge cases
    // =========================================================================

    public function test_make_from_manifest_throws_when_manifest_has_no_canvases(): void
    {
        $client  = Mockery::mock(IiifManifestClient::class);
        $factory = new DeiItemFactory($client);

        $parsed = new ParsedJsonLdData(
            fields: ['dc:title' => 'Manifest Story'],
            manifestUrl: 'https://example.com/iiif/manifest',
            pdfImage: '',
            externalRecordId: 'http://data.europeana.eu/item/2/test',
            recordId: '/2/test',
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IIIF manifest contains no canvases. Import aborted.');

        $factory->makeFromManifest($parsed, [
            'canvases'   => [],
            'imageLinks' => [],
        ]);
    }
}
