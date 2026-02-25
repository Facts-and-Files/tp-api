<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Export\AltoItemExporter;
use App\Services\Export\FileExportCache;
use App\Models\Item;
use App\Models\CacheExport;
use App\Models\Transcription;
use App\Services\Converter\HtmlToAltoConverter;
use Illuminate\Support\Facades\Storage;
use DOMDocument;

class AltoItemExporterTest extends TestCase
{
    private AltoItemExporter $service;

    protected function setUp(): void
    {
        parent::setUp();
        parent::seedAllItemRelatedTables();

        Storage::fake('export_cache');

        $this->service = new AltoItemExporter(
            new HtmlToAltoConverter,
            new FileExportCache
        );
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

        $this->assertStringContainsString('<alto xmlns=', $result);
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

        $this->assertStringContainsString('<alto xmlns=', $result);
        $this->assertStringContainsString('PrintSpace', $result);
        $this->assertStringContainsString('HEIGHT="5000"', $result);
        $this->assertStringContainsString('WIDTH="3533"', $result);

        $dom = new DOMDocument();
        $dom->loadXML($result);
        $this->assertEquals(0, $dom->getElementsByTagName('TextBlock')->length);
        $this->assertEquals(0, $dom->getElementsByTagName('String')->length);
    }

    public function test_creates_alto_from_page_xml_transcription()
    {
        $item = Item::find(7);

        $result = $this->service->exportToAlto($item);

        $this->assertStringContainsString('<alto xmlns=', $result);
        $this->assertStringContainsString('CONTENT="TestDescription"', $result);
    }

    public function test_returns_cached_alto_when_valid()
    {
        $item = Item::find(3);

        $firstResult = $this->service->exportToAlto($item);

        // Should use cache on second call
        $secondResult = $this->service->exportToAlto($item);

        $this->assertEquals($firstResult, $secondResult);
        $this->assertEquals(1, CacheExport::count());
    }

    public function test_invalidates_cache_when_item_transcription_updated()
    {
        $item = Item::find(3);

        $firstResult = $this->service->exportToAlto($item);

        // Update transcription
        $transcription = new Transcription();
        $transcription->UserId = 1;
        $transcription->ItemId = $item->ItemId;
        $transcription->Text = '<p>Updated content</p>';
        $transcription->TextNoTags = 'Updated content';
        $transcription->CurrentVersion = true;
        $transcription->Timestamp = now()->addSecond(); // change time at all
        $transcription->save();

        Transcription::where('ItemId', $item->ItemId)
            ->where('CurrentVersion', '=', true)
            ->where('TranscriptionId', '!=', $transcription->TranscriptionId)
            ->update(['CurrentVersion' => false]);

        $item->refresh();

        $secondResult = $this->service->exportToAlto($item);

        $this->assertNotEquals($firstResult, $secondResult);
        $this->assertStringContainsString('Updated', $secondResult);
        $this->assertStringContainsString('content', $secondResult);
    }
}
