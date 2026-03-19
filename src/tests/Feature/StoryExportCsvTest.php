<?php

namespace Tests\Feature;

use ZipArchive;
use League\Csv\Reader;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Tests\TestCase;

class StoryExportCsvTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => LanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionLanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemPropertyDataSeeder::class]);
    }

    public function test_get_a_non_existent_story_for_export_returns_404(): void
    {
        $awaitedSuccess = ['success' => false];
        $endpoint = '/stories/999999999999/items/export/txt';

        $response = $this->get($endpoint);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }
    public function test_invalid_export_format_returns_422(): void
    {
        $awaitedSuccess = ['success' => false];
        $endpoint = '/stories/1/items/export/exe';

        $response = $this->get($endpoint);

        $response
            ->assertStatus(422)
            ->assertJson($awaitedSuccess);
    }

    public function test_export_story_to_csv_returns_zip_with_csv_files(): void
    {
        $storyId = 1;
        $response = $this->get("/stories/{$storyId}/items/export/csv");

        $zipContent = $this->getZipContentFromResponse($response);
        $zip = new ZipArchive();
        $tempZip = $this->createTempZipFile($zipContent);

        $this->assertTrue($zip->open($tempZip));
        $this->assertEquals(3, $zip->numFiles);

        $this->assertStringContainsString("story-{$storyId}_", $zip->getNameIndex(0));
        $this->assertStringContainsString("story-{$storyId}_item-all_", $zip->getNameIndex(1));
        $this->assertStringContainsString("story-{$storyId}_item-all_properties_", $zip->getNameIndex(2));

        $zip->close();
        unlink($tempZip);
    }

    public function test_export_story_to_csv_contains_correct_story_data(): void
    {
        $response = $this->get('/stories/3/items/export/csv');

        $storyCsv = $this->extractFileFromZip($response, 0);
        $records = $this->parseCsv($storyCsv);

        $this->assertCount(1, $records);
        $this->assertEquals(3, $records[1]['StoryId']);
        $this->assertEquals('', $records[1]['RecordId']);
    }

    public function test_export_story_to_csv_contains_correct_items_data(): void
    {
        $response = $this->get('/stories/1/items/export/csv');

        $itemsCsv = $this->extractFileFromZip($response, 1);
        $records = $this->parseCsv($itemsCsv);
        $line = $records[1];

        $this->assertCount(1, $records);
        $this->assertEquals(1, $line['ItemId']);
        $this->assertStringContainsString('http', $line['ImageLink']);
        $this->assertEquals('German, English', $line['Transcription.Language']);
        $this->assertEquals('German', $line['Description.Language']);
        $this->assertEquals(ItemDataSeeder::$data[0]['Description'], $line['Description.Text']);
    }

    public function test_export_story_to_csv_contains_multiple_items_data(): void
    {
        $response = $this->get('/stories/3/items/export/csv');

        $itemsCsv = $this->extractFileFromZip($response, 1);
        $records = $this->parseCsv($itemsCsv);

        $this->assertCount(2, $records);
        $this->assertEquals(3, $records[1]['ItemId']);
        $this->assertEquals(5, $records[2]['ItemId']);
    }

    public function test_export_story_with_no_properties_returns_empty_properties_csv(): void
    {
        $response = $this->get('/stories/2/items/export/csv');
        $propertiesCsv = $this->extractFileFromZip($response, 2);

        $this->assertLessThanOrEqual(100, strlen($propertiesCsv));
    }

    public function test_export_story_to_csv_contains_multiple_properties_for_same_item(): void
    {
        $response = $this->get('/stories/1/items/export/csv');
        $propertiesCsv = $this->extractFileFromZip($response, 2);
        $records = $this->parseCsv($propertiesCsv);

        $itemProperties = array_filter($records, fn($record) => $record['ItemId'] == 1);

        $this->assertGreaterThan(0, count($itemProperties));

        foreach ($itemProperties as $property) {
            $this->assertArrayHasKey('ItemId', $property);
            $this->assertArrayHasKey('Type', $property);
            $this->assertArrayHasKey('Name', $property);
            $this->assertArrayHasKey('Description', $property);
        }
    }

    private function getZipContentFromResponse($response): string
    {
        ob_start();
        $response->sendContent();
        return ob_get_clean();
    }

    private function createTempZipFile(string $content): string
    {
        $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tempZip, $content);
        return $tempZip;
    }

    private function extractFileFromZip($response, int $index): string
    {
        $zipContent = $this->getZipContentFromResponse($response);
        $tempZip = $this->createTempZipFile($zipContent);

        $zip = new ZipArchive();
        $zip->open($tempZip);
        $content = $zip->getFromIndex($index);
        $zip->close();
        unlink($tempZip);

        return $content;
    }

    private function parseCsv(string $csvContent): array
    {
        $reader = Reader::fromString($csvContent);
        $reader->setHeaderOffset(0);
        return iterator_to_array($reader->getRecords());
    }
}
