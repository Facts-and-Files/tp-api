<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Export\AltoItemExporter;
use App\Services\ExportCache\FileExportCache;
use App\Models\Item;
use App\Models\CacheExport;
use App\Models\Transcription;
use App\Services\Converter\AltoConverterInterface;
use App\Services\Converter\HtmlToAltoConverter; // should work with PageXmlToAltoConverter too
use Illuminate\Support\Facades\Storage;

class AltoItemExporterTest extends TestCase
{
    private AltoItemExporter $service;
    private AltoConverterInterface $converter;

    protected function setUp(): void
    {
        parent::setUp();
        parent::seedAllItemRelatedTables();

        Storage::fake('export_cache');

        $this->service = new AltoItemExporter(
            new FileExportCache
        );

        $this->converter = new HtmlToAltoConverter();
    }

    public function test_creates_missing_cache_file()
    {
        $item = Item::find(5);

        $this->service->exportWithConverter($item, $this->converter);

        $cache = CacheExport::where('ItemId', $item->ItemId)->first();
        // delete cache first
        Storage::disk('export_cache')->delete($cache->FilePath);

        $result = $this->service->exportWithConverter($item, $this->converter);

        $this->assertStringContainsString('Page0000.tif', $result);
        $this->assertTrue(Storage::disk('export_cache')->exists($cache->FilePath));
    }

    public function test_creates_database_cache_entry()
    {
        $item = Item::find(1);

        $this->service->exportWithConverter($item, $this->converter);

        $this->assertDatabaseHas('CacheExport', [
            'ItemId' => $item->ItemId,
            'Format' => 'alto',
        ]);
    }

    public function test_creates_content_from_latest_transcription()
    {
        $item = Item::find(3);

        $result = $this->service->exportWithConverter($item, $this->converter);

        $this->assertStringContainsString('<alto xmlns=', $result);
        $this->assertStringContainsString('CONTENT="Example"', $result);
        $this->assertStringContainsString('CONTENT="Text"', $result);
    }

    public function test_returns_cached_alto_when_valid()
    {
        $item = Item::find(3);

        $firstResult = $this->service->exportWithConverter($item, $this->converter);

        // should use cache on second call
        $secondResult = $this->service->exportWithConverter($item, $this->converter);

        $this->assertEquals($firstResult, $secondResult);
        $this->assertEquals(1, CacheExport::count());
    }

    public function test_invalidates_cache_when_item_transcription_updated()
    {
        $item = Item::find(3);

        $firstResult = $this->service->exportWithConverter($item, $this->converter);

        // update transcription
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

        $secondResult = $this->service->exportWithConverter($item, $this->converter);

        $this->assertNotEquals($firstResult, $secondResult);
        $this->assertStringContainsString('Updated', $secondResult);
        $this->assertStringContainsString('content', $secondResult);
    }
}
