<?php

namespace Tests\Feature;

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
        $storyId = 1;
        $endpoint = "/stories/{$storyId}/items/export/yml";

        $response = $this->get($endpoint);
        $content = $response->streamedContent();
        $parsedYaml = Yaml::parse($content);

        $this->assertEquals($parsedYaml['StoryId'], 1);
        $this->assertEquals($parsedYaml['RecordId'], '');
        $this->assertIsArray($parsedYaml['Items']);
    }

    public function test_export_story_to_csv_returns_text_csv(): void
    {
        $storyId = 1;
        $endpoint = "/stories/{$storyId}/items/export/csv";

        $response = $this->get($endpoint);
        $disposition = $response->headers->get('Content-Disposition');

        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString("filename=story-{$storyId}-", $disposition);
        $this->assertStringContainsString(".csv", $disposition);
    }

    public function test_export_story_to_csv_has_content(): void
    {
        $storyId = 1;
        $endpoint = "/stories/{$storyId}/items/export/csv";

        $response = $this->get($endpoint);
        $content = $response->streamedContent();
        $csv = Reader::createFromString($content);
        $csv->setHeaderOffset(0);
        $data = iterator_to_array($csv->getRecords())[1];

        $this->assertEquals($data['StoryId'], 1);
        $this->assertEquals($data['RecordId'], '');
        $this->assertEquals($data['ItemIds.0'] ,1);
    }
}
