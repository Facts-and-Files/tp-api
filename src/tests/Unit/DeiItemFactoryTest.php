<?php

namespace Tests\Unit;

use App\Services\Import\DTO\ParsedJsonLdData;
use App\Services\Import\DeiItemFactory;
use App\Services\Import\IiifManifestClient;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DeiItemFactoryTest extends TestCase
{
    public function test_fetch_required_manifest_throws_when_manifest_url_is_missing(): void
    {
        $client = Mockery::mock(IiifManifestClient::class);
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
                'canvases' => [],
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

    public function test_make_from_manifest_returns_preview_image_and_items(): void
    {
        $client = Mockery::mock(IiifManifestClient::class);
        $factory = new DeiItemFactory($client);

        $parsed = new ParsedJsonLdData(
            fields: ['dc:title' => 'Manifest Story'],
            manifestUrl: 'https://example.com/iiif/manifest',
            pdfImage: '',
            externalRecordId: 'http://data.europeana.eu/item/2/test',
            recordId: '/2/test',
        );

        $manifest = [
            'canvases' => [
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
            $result['previewImage']
        );

        $this->assertCount(2, $result['items']);

        $this->assertSame('Manifest Story Item 1', $result['items'][0]['Title']);
        $this->assertSame(
            json_encode(['@id' => 'https://example.com/p1.jpg']),
            $result['items'][0]['ImageLink']
        );
        $this->assertSame(1, $result['items'][0]['OrderIndex']);
        $this->assertSame('https://example.com/iiif/manifest', $result['items'][0]['Manifest']);
        $this->assertSame('https://example.com/p1.jpg', $result['items'][0]['edm:WebResource']);

        $this->assertSame('Manifest Story Item 2', $result['items'][1]['Title']);
        $this->assertSame(
            json_encode(['@id' => 'https://example.com/p2.jpg']),
            $result['items'][1]['ImageLink']
        );
        $this->assertSame(2, $result['items'][1]['OrderIndex']);
        $this->assertSame('https://example.com/iiif/manifest', $result['items'][1]['Manifest']);
        $this->assertSame('https://example.com/p2.jpg', $result['items'][1]['edm:WebResource']);
    }

    public function test_make_from_manifest_throws_when_manifest_has_no_canvases(): void
    {
        $client = Mockery::mock(IiifManifestClient::class);
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
            'canvases' => [],
            'imageLinks' => [],
        ]);
    }
}
