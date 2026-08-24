<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ScoreDataSeeder;
use Database\Seeders\ScoreTypeDataSeeder;
use Database\Seeders\UserDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SummaryStatsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2023-04-15 12:00:00'),
        );

        Cache::flush();

        self::populateTable();
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public static function populateTable(): void
    {
        Artisan::call('db:seed', ['--class' => UserDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ScoreTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ScoreDataSeeder::class]);
    }

    public function test_current_month_can_be_refreshed_with_fresh_parameter(): void
    {
        CarbonImmutable::setTestNow('2023-03-15 12:00:00');

        Cache::flush();

        $first = $this->getJson('/statistics?Year=2023&Month=3');

        $first
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.Amount', 100);

        DB::table('Score')->insert([
            'ScoreId' => 6,
            'ItemId' => 1,
            'UserId' => 1,
            'ScoreTypeId' => 3,
            'Amount' => 50,
            'Timestamp' => '2023-03-10T12:00:00.000000Z',
        ]);

        $this->assertDatabaseHas('Score', [
            'ScoreId' => 6,
            'Amount' => 50,
        ]);

        $cached = $this->getJson(route('statistics', [
            'Year' => 2023,
            'Month' => 3,
        ]));

        $cached
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.Amount', 100);

        $fresh = $this->getJson(route('statistics', [
            'Year' => 2023,
            'Month' => 3,
            'fresh' => 1,
        ]));

        $fresh
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.Amount', 150);
    }

    public function test_get_all_monthly_based_statistics(): void
    {
        $awaitedData = [
            [
                'Year'                    => 2021,
                'Month'                   => 1,
                'ScoreTypeId'             => 2,
                'UniqueUsersPerScoreType' => 2,
                'UniqueItemsPerScoreType' => 2,
                'OverallUniqueUsers'      => 2,
                'OverallUniqueItems'      => 2,
                'OverallItemsStarted'     => 2,
                'Amount'                  => 57,
            ],
            [
                'Year'                    => 2022,
                'Month'                   => 2,
                'ScoreTypeId'             => 3,
                'UniqueUsersPerScoreType' => 1,
                'UniqueItemsPerScoreType' => 1,
                'OverallUniqueUsers'      => 1,
                'OverallUniqueItems'      => 1,
                'OverallItemsStarted'     => 1,
                'Amount'                  => 10,
            ],
            [
                'Year'                    => 2023,
                'Month'                   => 2,
                'ScoreTypeId'             => 3,
                'UniqueUsersPerScoreType' => 1,
                'UniqueItemsPerScoreType' => 1,
                'OverallUniqueUsers'      => 1,
                'OverallUniqueItems'      => 1,
                'OverallItemsStarted'     => 0,
                'Amount'                  => 100,
            ],
            [
                'Year'                    => 2023,
                'Month'                   => 3,
                'ScoreTypeId'             => 3,
                'UniqueUsersPerScoreType' => 1,
                'UniqueItemsPerScoreType' => 1,
                'OverallUniqueUsers'      => 1,
                'OverallUniqueItems'      => 1,
                'OverallItemsStarted'     => 1,
                'Amount'                  => 100,
            ],
            [
                'Year'                    => 2021,
                'Month'                   => 0,
                'ScoreTypeId'             => 2,
                'UniqueUsersPerScoreType' => 2,
                'UniqueItemsPerScoreType' => 2,
                'OverallUniqueUsers'      => 2,
                'OverallUniqueItems'      => 2,
                'OverallItemsStarted'     => 2,
                'Amount'                  => 57,
            ],
            [
                'Year'                    => 2022,
                'Month'                   => 0,
                'ScoreTypeId'             => 3,
                'UniqueUsersPerScoreType' => 1,
                'UniqueItemsPerScoreType' => 1,
                'OverallUniqueUsers'      => 1,
                'OverallUniqueItems'      => 1,
                'OverallItemsStarted'     => 1,
                'Amount'                  => 10,
            ],
            [
                'Year'                    => 2023,
                'Month'                   => 0,
                'ScoreTypeId'             => 3,
                'UniqueUsersPerScoreType' => 1,
                'UniqueItemsPerScoreType' => 2,
                'OverallUniqueUsers'      => 1,
                'OverallUniqueItems'      => 2,
                'OverallItemsStarted'     => 1,
                'Amount'                  => 200,
            ],
        ];

        $response = $this->get('/statistics');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => $awaitedData,
                'message' => 'Statistics fetched.',
            ]);
    }

    public function test_get_empty_statistics_by_year(): void
    {

        $response = $this->get('/statistics?Year=2024');

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => [],
                'message' => 'Statistics fetched.',
            ]);
    }

    public function test_get_statistics_by_year(): void
    {
        $awaitedData = [
            [
                'Year'                    => 2021,
                'Month'                   => 1,
                'ScoreTypeId'             => 2,
                'UniqueUsersPerScoreType' => 2,
                'UniqueItemsPerScoreType' => 2,
                'OverallUniqueUsers'      => 2,
                'OverallUniqueItems'      => 2,
                'OverallItemsStarted'     => 2,
                'Amount'                  => 57,
            ],
            [
                'Year'                    => 2021,
                'Month'                   => 0,
                'ScoreTypeId'             => 2,
                'UniqueUsersPerScoreType' => 2,
                'UniqueItemsPerScoreType' => 2,
                'OverallUniqueUsers'      => 2,
                'OverallUniqueItems'      => 2,
                'OverallItemsStarted'     => 2,
                'Amount'                  => 57,
            ],
        ];

        $response = $this->get('/statistics?Year=2021');

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => $awaitedData,
                'message' => 'Statistics fetched.',
            ]);
    }

    public function test_get_statistics_by_year_and_month(): void
    {
        $awaitedData = [
            [
                'Year'                    => 2021,
                'Month'                   => 1,
                'ScoreTypeId'             => 2,
                'UniqueUsersPerScoreType' => 2,
                'UniqueItemsPerScoreType' => 2,
                'OverallUniqueUsers'      => 2,
                'OverallUniqueItems'      => 2,
                'OverallItemsStarted'     => 2,
                'Amount'                  => 57,
            ],
        ];

        $response = $this->get('/statistics?Year=2021&Month=01');

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => $awaitedData,
                'message' => 'Statistics fetched.',
            ]);
    }

    public function test_get_statistics_by_year_month_and_score_type(): void
    {
        $awaitedData = [
            [
                'Year'                    => 2021,
                'Month'                   => 1,
                'ScoreTypeId'             => 2,
                'UniqueUsersPerScoreType' => 2,
                'UniqueItemsPerScoreType' => 2,
                'OverallUniqueUsers'      => 2,
                'OverallUniqueItems'      => 2,
                'OverallItemsStarted'     => 2,
                'Amount'                  => 57,
            ],
        ];

        $response = $this->get('/statistics?Year=2021&Month=01&ScoreTypeId=2');

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => $awaitedData,
                'message' => 'Statistics fetched.',
            ]);
    }

    public function test_current_month_statistics_are_cached_for_one_day(): void
    {
        CarbonImmutable::setTestNow('2023-03-15 12:00:00');

        Cache::flush();

        $first = $this->getJson('/statistics');

        $first->assertOk();

        DB::table('Score')->insert([
            'ScoreId' => 6,
            'ItemId' => 1,
            'UserId' => 1,
            'ScoreTypeId' => 3,
            'Amount' => 50,
            'Timestamp' => '2023-03-10T12:00:00.000000Z',
        ]);

        $second = $this->getJson(route('statistics'));

        $second->assertExactJson($first->json());
    }
}
