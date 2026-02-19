<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Export\AltoItemExporter;
use App\Models\Item;
use App\Models\CacheExport;
use App\Services\Converter\HtmlToAltoConverter;
use Illuminate\Support\Facades\Storage;
use DOMDocument;

class AltoItemExporterTest extends TestCase
{
    private AltoItemExporter $service;
    private HtmlToAltoConverter $htmlToAltoConverter;

    protected function setUp(): void
    {
        parent::setUp();
        parent::seedAllItemRelatedTables();

        Storage::fake('export_cache');

        $this->htmlToAltoConverter = new HtmlToAltoConverter;
        $this->service = new AltoItemExporter($this->htmlToAltoConverter);
    }

    public function test_creates_missing_cache_file()
    {
        $item = Item::find(5);

        $this->service->exportToAlto($item);

        $cache = CacheExport::where('ItemId', $item->ItemId)->first();
        // delete cache first
        Storage::disk('export_cache')->delete($cache->FilePath);

        $result = $this->service->exportToAlto($item);

        $this->assertStringContainsString('Page0000.tif', $result);
        $this->assertTrue(Storage::disk('export_cache')->exists($cache->FilePath));
    }

    public function test_creates_database_cache_entry()
    {
        $item = Item::find(1);

        $this->service->exportToAlto($item);

        $this->assertDatabaseHas('CacheExport', [
            'ItemId' => $item->ItemId,
            'Format' => 'alto',
        ]);
    }

    public function test_creates_alto_for_blank_html_transcriptions()
    {
        $item = Item::find(2);

        $result = $this->service->exportToAlto($item);

        $this->assertStringContainsString('alto', $result);
        $this->assertStringContainsString('PrintSpace', $result);
        $this->assertStringContainsString('HEIGHT="5000"', $result);
        $this->assertStringContainsString('WIDTH="3533"', $result);

        $dom = new DOMDocument();
        $dom->loadXML($result);
        $this->assertEquals(0, $dom->getElementsByTagName('TextBlock')->length);
        $this->assertEquals(0, $dom->getElementsByTagName('String')->length);
    }

    public function test_creates_alto_from_html_transcription()
    {
        $item = Item::find(3);

        $result = $this->service->exportToAlto($item);

        $this->assertStringContainsString('<alto xmlns=', $result);
        $this->assertStringContainsString('CONTENT="Example"', $result);
        $this->assertStringContainsString('CONTENT="Text"', $result);
    }

    public function test_creates_alto_for_blank_page_xml_transcriptions()
    {
        $item = Item::find(6);

        $result = $this->service->exportToAlto($item);

        $this->assertStringContainsString('alto', $result);
        $this->assertStringContainsString('PrintSpace', $result);
        $this->assertStringContainsString('HEIGHT="5000"', $result);
        $this->assertStringContainsString('WIDTH="3533"', $result);

        $dom = new DOMDocument();
        $dom->loadXML($result);
        $this->assertEquals(0, $dom->getElementsByTagName('TextBlock')->length);
        $this->assertEquals(0, $dom->getElementsByTagName('String')->length);
    }


/**
    public function it_generates_alto_from_page_xml()
    {
        $pageXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<PcGts xmlns="http://schema.primaresearch.org/PAGE/gts/pagecontent/2013-07-15">
    <Page imageFilename="test.jpg" imageWidth="1000" imageHeight="1500">
        <TextRegion id="region_1">
            <Coords points="100,100 900,100 900,200 100,200"/>
            <TextLine id="line_1">
                <Coords points="100,100 900,100 900,150 100,150"/>
                <TextEquiv>
                    <Unicode>Test transcription line</Unicode>
                </TextEquiv>
            </TextLine>
        </TextRegion>
    </Page>
</PcGts>
XML;

        $item = Item::factory()->create([
            'page_xml' => $pageXml,
            'html_transcription' => null,
        ]);

        $result = $this->service->exportToAlto($item);

        $this->assertStringContainsString('alto', $result);
        $this->assertStringContainsString('Test transcription line', $result);
        $this->assertStringContainsString('WIDTH="800"', $result);
        $this->assertStringContainsString('HEIGHT="1500"', $result);
    }

    public function it_caches_generated_alto()
    {
        $item = Item::factory()->create([
            'html_transcription' => '<p>Test content</p>',
        ]);

        $this->service->exportToAlto($item);

        $this->assertDatabaseHas('CacheExport', [
            'ItemId' => $item->ItemId,
            'Format' => 'alto',
        ]);

        $cache = CacheExport::where('ItemId', $item->ItemId)->first();
        $this->assertTrue(Storage::disk('export_cache')->exists($cache->FilePath));
    }

    public function it_returns_cached_alto_when_valid()
    {
        $item = Item::factory()->create([
            'html_transcription' => '<p>Original content</p>',
        ]);

        $firstResult = $this->service->exportToAlto($item);

        // Should use cache on second call
        $secondResult = $this->service->exportToAlto($item);

        $this->assertEquals($firstResult, $secondResult);
        $this->assertEquals(1, CacheExport::count());
    }

    public function it_invalidates_cache_when_item_updated()
    {
        $item = Item::factory()->create([
            'html_transcription' => '<p>Original content</p>',
        ]);

        $firstResult = $this->service->exportToAlto($item);

        // Update item
        $item->html_transcription = '<p>Updated content</p>';
        $item->save();
        $item->refresh();

        $secondResult = $this->service->exportToAlto($item);

        $this->assertNotEquals($firstResult, $secondResult);
        $this->assertStringContainsString('Updated content', $secondResult);
    }
**/
}
