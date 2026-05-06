<?php

namespace Tests\Feature;

use Database\Seeders\HtrDataDataSeeder;
use Database\Seeders\HtrDataRevisionDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ItemExportTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable(): void
    {
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => LanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionLanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemPropertyDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => HtrDataDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => HtrDataRevisionDataSeeder::class]);
    }

    public function test_export_item_to_alto_returns_xml_with_filename(): void
    {
        $itemId = 3;
        $endpoint = "/items/{$itemId}/export/alto";

        $response = $this->get($endpoint);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $disposition = $response->headers->get('Content-Disposition');

        // Adjust pattern to your filename factory, this just checks story/item IDs appear
        $this->assertStringContainsString("item-{$itemId}", $disposition);
        $this->assertStringContainsString(".xml", $disposition);
    }

    public function test_export_item_to_unsupported_format(): void
    {
        $itemId = 3;
        $endpoint = "/items/{$itemId}/export/unsupported";

        $response = $this->get($endpoint);

        $response->assertStatus(422);
    }

    public function test_export_item_to_alto_contains_correct_metadata(): void
    {
        $itemId = 3;
        $endpoint = "/items/{$itemId}/export/alto";

        $response = $this->get($endpoint);

        $response->assertOk();

        $xmlString = $response->streamedContent();

        $this->assertNotEmpty($xmlString);

        $xml = simplexml_load_string($xmlString);
        $this->assertNotFalse($xml, 'Result is not valid XML');

        $xml->registerXPathNamespace('alto', 'http://www.loc.gov/standards/alto/ns-v4#');

        $measurementUnit = $xml->xpath('//alto:MeasurementUnit');
        $this->assertNotEmpty($measurementUnit);
        $this->assertSame('pixel', (string) $measurementUnit[0]);

        $fileName = $xml->xpath('//alto:sourceImageInformation/alto:fileName');
        $fileIdentifier = $xml->xpath('//alto:sourceImageInformation/alto:fileIdentifier');

        $this->assertNotEmpty($fileName);
        $this->assertNotEmpty($fileIdentifier);

        $page = $xml->xpath('//alto:Layout/alto:Page');
        $this->assertNotEmpty($page);

        $attributes = $page[0]->attributes();
        $this->assertGreaterThan(0, (int) $attributes['WIDTH']);
        $this->assertGreaterThan(0, (int) $attributes['HEIGHT']);
    }

    public function test_export_item_to_pagexml_returns_converted_pagexml(): void
    {
        $itemId = 1;
        $endpoint = "/items/{$itemId}/export/pagexml";

        $response = $this->get($endpoint);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $xmlString = $response->streamedContent();

        $this->assertNotEmpty($xmlString);
        $this->assertStringContainsString('<PcGts', $xmlString);

        $xml = simplexml_load_string($xmlString);
        $this->assertNotFalse($xml, 'Result is not valid XML');

        $xml->registerXPathNamespace(
            'page',
            'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15',
        );

        $creator = $xml->xpath('//page:Metadata/page:Creator');
        $page = $xml->xpath('//page:Page');

        $this->assertNotEmpty($page);
        $this->assertNotEmpty($creator);
        $this->assertSame('Transcribathon API Exporter', (string) $creator[0]);
    }

    public function test_export_item_to_pagexml_returns_existing_htr_pagexml(): void
    {
        $itemId = 7;
        $endpoint = "/items/{$itemId}/export/pagexml";

        $response = $this->get($endpoint);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $xmlString = $response->streamedContent();

        $this->assertNotEmpty($xmlString);
        $this->assertStringContainsString('<PcGts', $xmlString);

        $xml = simplexml_load_string($xmlString);
        $this->assertNotFalse($xml, 'Result is not valid XML');

        $xml->registerXPathNamespace(
            'page',
            'http://schema.primaresearch.org/PAGE/gts/pagecontent/2013-07-15',
        );

        $creator = $xml->xpath('//page:Metadata/page:Creator');
        $page = $xml->xpath('//page:Page');

        $this->assertNotEmpty($page);
        $this->assertNotEmpty($creator);
        $this->assertSame('Transkribus Processing API', (string) $creator[0]);
    }
}
