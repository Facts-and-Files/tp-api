<?php

namespace Tests\Feature;

use Database\Seeders\ScoreDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SummaryStatsTest extends TestCase
{
    private static $endpoint = '/statistics';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => ScoreDataSeeder::class]);
    }

    public function testGetAllMonthlyBasedStatistics(): void
    {
        $queryParams = '';
        $awaitedSuccess = ['success' => true];
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
                'Amount'                  => 57
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
                'Amount'                  => 10
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
                'Amount'                  => 57
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
                'Amount'                  => 10
            ]
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess);

        $this->assertEquals($response['data'], $awaitedData);
    }

    public function testGetEmptyStatisticsByYear(): void
    {
        $queryParams = '?Year=2024';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => []];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetStatisticsByYear(): void
    {
        $queryParams = '?Year=2021';
        $awaitedSuccess = ['success' => true];
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
                'Amount'                  => 57
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
                'Amount'                  => 57
            ]
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess);

        $this->assertEquals($response['data'], $awaitedData);
    }

    public function testGetStatisticsByYearAndMonth(): void
    {
        $queryParams = '?Year=2021&Month=01';
        $awaitedSuccess = ['success' => true];
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
                'Amount'                  => 57
            ]
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess);

        $this->assertEquals($response['data'], $awaitedData);
    }

    public function testGetStatisticsByYearMonthAndScoreType(): void
    {
        $queryParams = '?Year=2021&Month=01&ScoreTypeId=2';
        $awaitedSuccess = ['success' => true];
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
                'Amount'                  => 57
            ]
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess);

        $this->assertEquals($response['data'], $awaitedData);
    }
}
