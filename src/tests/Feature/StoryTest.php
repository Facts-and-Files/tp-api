<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\ProjectDataSeeder;
use Tests\TestCase;

class StoryTest extends TestCase
{
    private static $endpoint = 'stories';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable(): void
    {
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ProjectDataSeeder::class]);
    }

    public function test_get_all_stories(): void
    {
        $awaitedSuccess = ['success' => true];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJsonCount(3, 'data');
    }

    public function test_get_a_non_existent_story_returns_404(): void
    {
        $queryParams = '/999999999999';
        $awaitedSuccess = ['success' => false];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function test_get_basic_data_of_a_single_story(): void
    {
        $queryParams = '/' . StoryDataSeeder::$data[0]['StoryId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = StoryDataSeeder::$data[0];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJsonFragment(['StoryId' => $awaitedData['StoryId']]);
    }

    public function test_update_data_of_a_single_story(): void
    {
        $queryParams = '/' . StoryDataSeeder::$data[0]['StoryId'];
        $awaitedSuccess = ['success' => true];
        $updateData = [
            'Dc' => ['Title' => 'Updated-Test-Title'],
            'Place' => [
                'Name' => 'Updated-Name',
                'WikiDataId' => 'Updated-WikidataId',
            ],
        ];

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson(['data' => $updateData]);
    }
}
