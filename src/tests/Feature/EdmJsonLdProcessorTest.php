<?php

namespace Tests\Feature;

use App\Services\JsonLd\EdmJsonLdProcessor;
use App\Services\JsonLd\NodeIndexer;
use App\Services\JsonLd\LiteralResolver;
use App\Services\JsonLd\FieldExtractor;
use App\Exceptions\InvalidManifestUrlException;
use Tests\TestCase;

class EdmJsonLdProcessorTest extends TestCase
{
    private EdmJsonLdProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();


        $this->processor = new EdmJsonLdProcessor(
            new NodeIndexer(),
            new LiteralResolver(),
            new FieldExtractor()
        );
    }

    protected function loadFixture(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/data/edm-complete.json'), true);
    }

    public function test_throws_exception_for_invalid_manifest_url(): void
    {
        config(['edm.mappings' => [
            'manifestUrl' => [
                'paths' => [
                    ['path' => 'iiif_url', 'type' => null],
                ]
            ]
        ]]);

        $data = ['iiif_url' => 'not-a-valid-url'];

        $this->expectException(InvalidManifestUrlException::class);
        $this->processor->processJsonLd($data);
    }

    public function test_processes_full_jsonld_fixture_correctly(): void
    {
        $jsonLdData = $this->loadFixture();

        $processedData = $this->processor->processJsonLd($jsonLdData);

        // Assert main keys exist
        $this->assertArrayHasKey('story', $processedData);
        $this->assertArrayHasKey('recordId', $processedData);
        $this->assertArrayHasKey('externalRecordId', $processedData);
        $this->assertArrayHasKey('storyTitle', $processedData);
        $this->assertArrayHasKey('manifestUrl', $processedData);
        $this->assertArrayHasKey('imageLink', $processedData);
        $this->assertArrayHasKey('pdfImage', $processedData);
        $this->assertArrayHasKey('imageLinks', $processedData);
        $this->assertArrayHasKey('placeAdded', $processedData);

        // Assert specific representative values from the fixture
        $this->assertIsArray($processedData['story']);
        $this->assertSame('http://example.org/manifest/test_1', $processedData['manifestUrl']);
        $this->assertSame('http://example.org/item/test_1', $processedData['externalRecordId']);
        $this->assertSame('item/test_1', $processedData['recordId']);

        $this->assertFalse($processedData['placeAdded']); // our test fixture has no place coordinates

        // Optional: assert that story array contains expected keys (if your processor populates them)
        // $expectedStoryKeys = ['dc:title', 'dc:description', 'edm:agent', 'edm:currentLocation'];
        // foreach ($expectedStoryKeys as $key) {
        //     $this->assertArrayHasKey($key, $processedData['story'] ?? [], "Story should contain {$key}");
        // }
    }
}
