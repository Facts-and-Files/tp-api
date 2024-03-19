<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class UserStatsTest extends TestCase
{
    private static $scoreTypeTableName = 'ScoreType';

    private static $scoreTypeTableData = [
        [
            'ScoreTypeId' => 1,
            'Name'        => 'Location',
            'Rate'        => 0.2
        ],
        [
            'ScoreTypeId' => 2,
            'Name'        => 'Transcription',
            'Rate'        => 0.0033
        ],
        [
            'ScoreTypeId' => 3,
            'Name'        => 'Enrichment',
            'Rate'        => 0.2
        ],
        [
            'ScoreTypeId' => 4,
            'Name'        => 'Description',
            'Rate'        => 0.2
        ],
        [
            'ScoreTypeId' => 5,
            'Name'        => 'HTR-Transcription',
            'Rate'        => 0.0033
        ]
    ];

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
            'Amount'      => 0,
            'Timestamp'   => '2021-02-01T12:00:00.000000Z'
        ],
        [
            'ScoreId'     => 3,
            'ItemId'      => 3,
            'UserId'      => 1,
            'ScoreTypeId' => 3,
            'Amount'      => 0,
            'Timestamp'   => '2021-03-01T12:00:00.000000Z'
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        DB::table(self::$scoreTypeTableName)->insertOrIgnore(self::$scoreTypeTableData);
        DB::table(self::$tableName)->insert(self::$tableData);
    }

    public function testGetNotFoundOnNonExistentUser(): void
    {
        $userId = 0;
        $endpoint = '/users/' . $userId . '/statistics';
        $queryParams = '?limit=1&page=1&orderBy=ScoreId&orderDir=desc';
        $awaitedSuccess = ['success' => false];

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function testGetStatisticsForAnUser(): void
    {
        $userId = 1;
        $endpoint = '/users/' . $userId . '/statistics';
        $queryParams = '?limit=1&page=1&orderBy=ScoreId&orderDir=desc';
        $awaitedSuccess = ['success' => true];
        $userFiltered = array_filter(self::$tableData, function($entry) use ($userId) {
            return $entry['UserId'] == $userId;
        });
        function scoreFiltered (array $array, int $scoreTypeId): int
        {
            return array_sum(
                array_column(
                    array_filter($array, function($entry) use ($scoreTypeId) {
                        return $entry['ScoreTypeId'] === $scoreTypeId;
                    }),
                    'Amount'
                )
            );
        }
        $awaitedData = [
            'data' => [
                'UserId'               => $userId,
                'Items'                => count(
                    array_unique(array_column(array_filter($userFiltered), 'ItemId'))
                ),
                'Locations'            => scoreFiltered($userFiltered, 1),
                'ManualTranscriptions' => scoreFiltered($userFiltered, 2),
                'Enrichments'          => scoreFiltered($userFiltered, 3),
                'Descriptions'         => scoreFiltered($userFiltered, 4),
                'HTRTranscriptions'    => scoreFiltered($userFiltered, 5)
            ]
        ];
        function rate (array $array, int $scoreTypeId): float
        {
            $filtered = array_filter($array, function ($entry) use ($scoreTypeId) {
                return $entry['ScoreTypeId'] === $scoreTypeId;
            });
            $filtered = reset($filtered);

            return $filtered['Rate'];
        }
        $awaitedData['data']['Miles'] = ceil(
            $awaitedData['data']['Locations']            * rate(self::$scoreTypeTableData, 1) +
            $awaitedData['data']['ManualTranscriptions'] * rate(self::$scoreTypeTableData, 2) +
            $awaitedData['data']['Enrichments']          * rate(self::$scoreTypeTableData, 3) +
            $awaitedData['data']['Descriptions']         * rate(self::$scoreTypeTableData, 4) +
            $awaitedData['data']['HTRTranscriptions']    * rate(self::$scoreTypeTableData, 5)
        );

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
