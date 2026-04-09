<?php

namespace Tests\Feature;

use Symfony\Component\Yaml\Yaml;
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

class StoryExportYamlTest extends TestCase
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
    }

    public function test_export_story_to_yaml_returns_application_yaml(): void
    {
        $storyId = 1;
        $endpoint = "/stories/{$storyId}/items/export/yml";

        $response = $this->get($endpoint);
        $disposition = $response->headers->get('Content-Disposition');

        $response->assertOk()->assertHeader('Content-Type', 'application/yaml; charset=utf-8');
        $this->assertStringContainsString("filename=transcribathon_story-{$storyId}_", $disposition);
        $this->assertStringContainsString(".yml", $disposition);
    }

    public function test_export_story_to_yaml_returns_correct_story_data(): void
    {
        $storyId = 3;
        $endpoint = "/stories/{$storyId}/items/export/yml";

        $response = $this->get($endpoint);
        $content = $response->streamedContent();
        $parsedYaml = Yaml::parse($content);

        $this->assertEquals($storyId, $parsedYaml['StoryId']);
        $this->assertEquals('', $parsedYaml['RecordId']);
    }

    public function test_export_story_to_yaml_returns_correct_items_data(): void
    {
        $storyId = 3;
        $endpoint = "/stories/{$storyId}/items/export/yml";

        $response = $this->get($endpoint);
        $content = $response->streamedContent();
        $parsedYaml = Yaml::parse($content);

        $this->assertEquals(3, $parsedYaml['Items'][0]['ItemId']);
        $this->assertEquals('German', $parsedYaml['Items'][0]['Description']['Language'][0]);
    }

    public function test_export_story_to_yaml_returns_correct_item_description_data(): void
    {
        $storyId = 3;
        $endpoint = "/stories/{$storyId}/items/export/yml";

        $response = $this->get($endpoint);
        $content = $response->streamedContent();
        $parsedYaml = Yaml::parse($content);

        $this->assertEquals('German', $parsedYaml['Items'][0]['Description']['Language'][0]);
        $this->assertEquals(ItemDataSeeder::$data[2]['Description'], $parsedYaml['Items'][0]['Description']['Text']);
    }

    public function test_export_story_to_yaml_return_correct_transcriptions_data(): void
    {
        $storyId = 3;
        $endpoint = "/stories/{$storyId}/items/export/yml";

        $response = $this->get($endpoint);
        $content = $response->streamedContent();
        $parsedYaml = Yaml::parse($content);

        $this->assertEquals('German', $parsedYaml['Items'][0]['Transcription']['Language'][0]);
        $this->assertEquals('English', $parsedYaml['Items'][0]['Transcription']['Language'][1]);
    }

    public function test_export_story_to_yaml_returns_correct_properties_data(): void
    {
        $storyId = 3;
        $endpoint = "/stories/{$storyId}/items/export/yml";

        $response = $this->get($endpoint);
        $content = $response->streamedContent();
        $parsedYaml = Yaml::parse($content);
        $property = $parsedYaml['Items'][0]['Properties'][0];

        $this->assertEquals('Language', $property['Type']);
        $this->assertEquals('German', $property['Name']);
        $this->assertEquals('Description for Test Property 1', $property['Description']);
    }
}
