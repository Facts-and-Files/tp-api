<?php

namespace Tests\Feature;

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

class ItemExportPageXmlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => LanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionLanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemPropertyDataSeeder::class]);
    }

    public function test_export_item_to_pagexml_returns_xml_with_filename(): void
    {
        $itemId = 3;
        $endpoint = "/items/{$itemId}/export/pagexml";

        $response = $this->get($endpoint);
        /* dump($response); */

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("item-{$itemId}", $disposition);
        $this->assertStringContainsString('.xml', $disposition);
    }

    public function test_export_item_to_pagexml_contains_correct_metadata(): void
    {
        $itemId = 3;
        $endpoint = "/items/{$itemId}/export/pagexml";

        $response = $this->get($endpoint);
        $response->assertOk();

        $xmlString = $response->streamedContent();
        $this->assertNotEmpty($xmlString);

        $xml = simplexml_load_string($xmlString);
        $this->assertNotFalse($xml, 'Result is not valid XML');
        $xml->registerXPathNamespace('page', 'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15');

        $page = $xml->xpath('//page:Page');
        $this->assertNotEmpty($page);

        $attributes = $page[0]->attributes();
        $this->assertGreaterThan(0, (int) $attributes['imageWidth']);
        $this->assertGreaterThan(0, (int) $attributes['imageHeight']);

        $metadata = $xml->xpath('//page:Metadata/page:Creator');
        $this->assertNotEmpty($metadata);
    }

    public function test_export_item_to_pagexml_contains_regions(): void
    {
        $itemId = 3;
        $endpoint = "/items/{$itemId}/export/pagexml";

        $response = $this->get($endpoint);
        $response->assertOk();

        $xml = simplexml_load_string($response->streamedContent());
        $xml->registerXPathNamespace('page', 'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15');

        $regions = $xml->xpath('//page:TextRegion | //page:TableRegion');
        $this->assertNotEmpty($regions);
    }
}
