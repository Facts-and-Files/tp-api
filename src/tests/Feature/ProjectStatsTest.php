<?php

namespace Tests\Feature;

use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ProjectDataSeeder;
use Database\Seeders\ScoreDataSeeder;
use Database\Seeders\ScoreTypeDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\UserDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProjectStatsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        self::populate_tables();
    }

    public static function populate_tables(): void
    {
        Artisan::call('db:seed', ['--class' => ProjectDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => UserDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ScoreTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ScoreDataSeeder::class]);
    }

    public function test_get_not_found_on_non_existent_project(): void
    {
        $project_id = 9999;
        $endpoint = '/projects/' . $project_id . '/statistics';

        $response = $this->get($endpoint);

        $response
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Not found',
                'data' => 'No statistics exists for this Project yet.',
            ]);
    }

    public function test_get_statistics_for_a_project(): void
    {
        $project_id = 1;
        $endpoint = '/projects/' . $project_id . '/statistics';

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Projects statistics fetched.',
                'data' => [
                    'ProjectId' => 1,
                    'Summary' => [
                        'Stories' => 2,
                        'Items' => 2,
                        'Locations' => 0,
                        'ManualTranscriptions' => 57,
                        'Enrichments' => 100,
                        'Descriptions' => 0,
                        'HTRTranscriptions' => 0,
                        'Miles' => 22,
                    ],
                    'Teams' => [],
                    'Users' => [
                        [
                            'Stories' => 1,
                            'Items' => 1,
                            'Locations' => 0,
                            'ManualTranscriptions' => 55,
                            'Enrichments' => 100,
                            'Descriptions' => 0,
                            'HTRTranscriptions' => 0,
                            'Miles' => 21,
                            'UserId' => 1,
                        ],
                        [
                            'Stories' => 1,
                            'Items' => 1,
                            'Locations' => 0,
                            'ManualTranscriptions' => 2,
                            'Enrichments' => 0,
                            'Descriptions' => 0,
                            'HTRTranscriptions' => 0,
                            'Miles' => 1,
                            'UserId' => 2,
                        ],
                    ],
                ],
            ]);
    }

    public function test_get_statistics_for_a_project_has_expected_structure(): void
    {
        $project_id = 1;
        $endpoint = '/projects/' . $project_id . '/statistics';

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'ProjectId',
                    'Summary' => [
                        'Stories',
                        'Items',
                        'Locations',
                        'ManualTranscriptions',
                        'Enrichments',
                        'Descriptions',
                        'HTRTranscriptions',
                        'Miles',
                    ],
                    'Teams',
                    'Users' => [
                        '*' => [
                            'UserId',
                            'Stories',
                            'Items',
                            'Locations',
                            'ManualTranscriptions',
                            'Enrichments',
                            'Descriptions',
                            'HTRTranscriptions',
                            'Miles',
                        ],
                    ],
                ],
                'message',
            ]);
    }
}
