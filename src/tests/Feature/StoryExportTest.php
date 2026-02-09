<?php

namespace Tests\Feature;

use ZipArchive;
use Symfony\Component\Yaml\Yaml;
use League\Csv\Reader;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Tests\TestCase;

class StoryExportTest extends TestCase
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
        Artisan::call('db:seed', ['--class' => ItemPropertyDataSeeder::class]);
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

    public function test_export_story_to_yml_returns_application_yaml(): void
    {
        $storyId = 1;
        $endpoint = "/stories/{$storyId}/items/export/yml";

        $response = $this->get($endpoint);
        $disposition = $response->headers->get('Content-Disposition');

        $response->assertOk()->assertHeader('Content-Type', 'application/yaml; charset=utf-8');
        $this->assertStringContainsString("filename=story-{$storyId}-", $disposition);
        $this->assertStringContainsString(".yml", $disposition);
    }

    public function test_export_story_to_yaml_has_content(): void
    {
        $storyId = 3;
        $endpoint = "/stories/{$storyId}/items/export/yml";

        $response = $this->get($endpoint);
        $content = $response->streamedContent();
        $parsedYaml = Yaml::parse($content);

        $this->assertEquals($storyId, $parsedYaml['StoryId']);
        $this->assertEquals(3, $parsedYaml['Items'][0]['ItemId']);
        $this->assertEquals('German, English', $parsedYaml['Items'][0]['Transcription']['Language']);
    }

    public function test_export_story_to_csv_returns_zip_file(): void
    {
        $response = $this->get('/stories/1/items/export/csv');

        $zipContent = $this->getZipContentFromResponse($response);
        $zip = new ZipArchive();
        $tempZip = $this->createTempZipFile($zipContent);

        $this->assertTrue($zip->open($tempZip));
        $this->assertEquals(2, $zip->numFiles);

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
        $this->assertEquals(3, $records[1]['ItemIds.0']);
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
}
