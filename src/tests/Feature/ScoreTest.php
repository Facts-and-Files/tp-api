<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ScoreTest extends TestCase
{
    private static $endpoint = '/scores';

    private static $tableName = 'Score';

    private static $tableData = [
        [
            'ScoreId'     => 1,
            'ItemId'      => 1,
            'UserId'      => 1,
            'ScoreTypeId' => 2,
            'Amount'      => 1,
            'Timestamp'   => '2021-01-01T00:00:00.000000Z'
        ],
        [
            'ScoreId'     => 2,
            'ItemId'      => 2,
            'UserId'      => 2,
            'ScoreTypeId' => 2,
            'Amount'      => 2,
            'Timestamp'   => '2021-01-02T00:00:00.000000Z'
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

    public function testGetAllScores(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllScoresLimitedAndSorted(): void
    {
        $queryParams = '?limit=1&page=1&orderBy=ScoreId&orderDir=desc';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllScoredByUserId(): void
    {
        $queryParams = '?UserId='. self::$tableData[1]['UserId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllScoredByItemId(): void
    {
        $queryParams = '?ItemId='. self::$tableData[1]['ItemId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllScoredByScoreTypeId(): void
    {
        $queryParams = '?ScoreTypeId='. self::$tableData[1]['ScoreTypeId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testCreateAScore(): void
    {
        $createData = [
            'ScoreId' => 3,
            'ItemId' => 438268,
            'UserId' => 3,
            'ScoreTypeId' => 3,
            'Amount' => 3,
            'Timestamp' => '2021-01-03T00:00:00.000000Z'
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
