<?php

namespace Tests\Feature;

use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ProjectDataSeeder;
use Database\Seeders\ScoreTypeDataSeeder;
use Database\Seeders\ScoreDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UserItemsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => ProjectDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ScoreTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ScoreDataSeeder::class]);
    }

    public function test_non_existent_user_returns_empty(): void
    {
        $userId = 0;
        $endpoint = '/users/' . $userId . '/items';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => []];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_latest_items_of_an_user(): void
    {
        $userId = 1;
        $endpoint = '/users/' . $userId . '/items';
        $queryParams = '';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [
            [
                'ProjectName' => 'Project-1',
                'Items' => [
                    [
                        'ItemId' => 1,
                        'ItemTitle' => '',
                        'ItemImageLink' => ItemDataSeeder::$data[0]['ImageLink'],
                        'CompletionStatus' => 'Not Started',
                        'LastEdit' => '2023-02-01T12:00:00.000000Z',
                        'Scores' => [
                            [
                                'ScoreType' => 'Enrichment',
                                'Amount' => 100
                            ],
                            [
                                'ScoreType' => 'Transcription',
                                'Amount' => 55
                            ],
                        ],
                    ]
                ],
            ],
            [
                'ProjectName' => 'Project-2',
                'Items' => [
                    [
                        'ItemId' => 5,
                        'ItemTitle' => '',
                        'ItemImageLink' => ItemDataSeeder::$data[3]['ImageLink'],
                        'CompletionStatus' => 'Not Started',
                        'LastEdit' => '2023-03-01T12:00:00.000000Z',
                        'Scores' => [
                            [
                                'ScoreType' => 'Enrichment',
                                'Amount' => 100
                            ],
                        ],
                    ],
                    [
                        'ItemId' => 3,
                        'ItemTitle' => '',
                        'ItemImageLink' => ItemDataSeeder::$data[2]['ImageLink'],
                        'CompletionStatus' => 'Not Started',
                        'LastEdit' => '2022-02-01T12:00:00.000000Z',
                        'Scores' => [
                            [
                                'ScoreType' => 'Enrichment',
                                'Amount' => 10
                            ],
                        ],
                    ],
                ],
            ],
        ]];

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_latest_items_of_an_user_limited(): void
    {
        $userId = 1;
        $endpoint = '/users/' . $userId . '/items';
        $queryParams = '?limit=1&page=2&threshold=100,';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [
            [
                'ProjectName' => 'Project-1',
                'Items' => [],
            ],
            [
                'ProjectName' => 'Project-2',
                'Items' => [
                    [
                        'ItemId' => 3,
                        'ItemTitle' => '',
                        'ItemImageLink' => ItemDataSeeder::$data[2]['ImageLink'],
                        'CompletionStatus' => 'Not Started',
                        'LastEdit' => '2022-02-01T12:00:00.000000Z',
                        'Scores' => [
                            [
                                'ScoreType' => 'Enrichment',
                                'Amount' => 10
                            ],
                        ],
                    ],
                ],
            ],
        ]];
        $awaitedMeta = ['meta' => [
            'limit' => 1,
            'currentPage' => 2,
            'threshold' => 100,
        ]];

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData)
            ->assertJson($awaitedMeta);
    }
}
