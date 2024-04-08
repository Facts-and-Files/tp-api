<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SummaryStatsTest extends TestCase
{
    private static $endpoint = '/statistics';

    private static $tableName = 'Score';


    private static $tableData = [
        [
            'ScoreId'     => 1,
            'ItemId'      => 1,
            'UserId'      => 1,
            'ScoreTypeId' => 2,
            'Amount'      => 55,
            'Timestamp'   => '2021-01-01T12:00:00.000000Z'
        ],
        [
            'ScoreId'     => 2,
            'ItemId'      => 2,
            'UserId'      => 2,
            'ScoreTypeId' => 2,
            'Amount'      => 2,
            'Timestamp'   => '2021-01-01T12:00:00.000000Z'
        ],
        [
            'ScoreId'     => 3,
            'ItemId'      => 3,
            'UserId'      => 1,
            'ScoreTypeId' => 3,
            'Amount'      => 10,
            'Timestamp'   => '2021-02-01T12:00:00.000000Z'
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        DB::table(self::$tableName)->insert(self::$tableData);
    }

    public function testGetAllMonthlyBasedStatistics(): void
    {
        $queryParams = '';
        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                [
                    'Year'                    => 2021,
                    'Month'                   => 1,
                    'ScoreTypeId'             => 2,
                    'UniqueUsersPerScoreType' => 2,
                    'OverallUniqueUsers'      => 2,
                    'Amount'                  => 57
                ],
                [
                    'Year'                    => 2021,
                    'Month'                   => 2,
                    'ScoreTypeId'             => 3,
                    'UniqueUsersPerScoreType' => 1,
                    'OverallUniqueUsers'      => 1,
                    'Amount'                  => 10
                ]
            ]
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
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
            'data' => [
                [
                    'Year'                    => 2021,
                    'Month'                   => 1,
                    'ScoreTypeId'             => 2,
                    'UniqueUsersPerScoreType' => 2,
                    'OverallUniqueUsers'      => 2,
                    'Amount'                  => 57
                ]
            ]
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetStatisticsByYearAndMonth(): void
    {
        $queryParams = '?Year=2021&Month=01';
        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                [
                    'Year'                    => 2021,
                    'Month'                   => 1,
                    'ScoreTypeId'             => 2,
                    'UniqueUsersPerScoreType' => 2,
                    'OverallUniqueUsers'      => 2,
                    'Amount'                  => 57
                ]
            ]
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetStatisticsByYearMonthAndScoreType(): void
    {
        $queryParams = '?Year=2021&Month=01&ScoreTypeId=2';
        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                [
                    'Year'                    => 2021,
                    'Month'                   => 1,
                    'ScoreTypeId'             => 2,
                    'UniqueUsersPerScoreType' => 2,
                    'OverallUniqueUsers'      => 2,
                    'Amount'                  => 57
                ]
            ]
        ];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
