<?php

namespace Tests\Feature;

use Database\Seeders\ScoreDataSeeder;
use Database\Seeders\ScoreTypeDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UserStatsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => ScoreTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ScoreDataSeeder::class]);
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

        $userFiltered = array_filter(ScoreDataSeeder::$data, function($entry) use ($userId) {
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
            $awaitedData['data']['Locations']            * rate(ScoreTypeDataSeeder::$data, 1) +
            $awaitedData['data']['ManualTranscriptions'] * rate(ScoreTypeDataSeeder::$data, 2) +
            $awaitedData['data']['Enrichments']          * rate(ScoreTypeDataSeeder::$data, 3) +
            $awaitedData['data']['Descriptions']         * rate(ScoreTypeDataSeeder::$data, 4) +
            $awaitedData['data']['HTRTranscriptions']    * rate(ScoreTypeDataSeeder::$data, 5)
        );

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    function testGetStatisticsForAllUsers(): void
    {
        $endpoint = '/users/statistics';
        $queryParams = '';
        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                [
                    'UserId' => 1,
                    'Items' => 3,
                    'Locations' => 0,
                    'ManualTranscriptions' => 55,
                    'Enrichments' => 210,
                    'Descriptions' => 0,
                    'HTRTranscriptions' => 0,
                    'Miles' => 43,
                ],
                [
                    'UserId' => 2,
                    'Items' => 1,
                    'Locations' => 0,
                    'ManualTranscriptions' => 2,
                    'Enrichments' => 0,
                    'Descriptions' => 0,
                    'HTRTranscriptions' => 0,
                    'Miles' => 1,
                ]
            ]
        ];

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetStatisticsForAllUsersLimitedAndSorted(): void
    {
        $endpoint = '/users/statistics';
        $queryParams = '?limit=1&page=1&orderBy=ManualTranscriptions&orderDir=asc';
        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                [
                    'UserId' => 2,
                    'Items' => 1,
                    'Locations' => 0,
                    'ManualTranscriptions' => 2,
                    'Enrichments' => 0,
                    'Descriptions' => 0,
                    'HTRTranscriptions' => 0,
                    'Miles' => 1,
                ]
            ]
        ];
        $awaitedMeta = [
            'meta' => [
                'limit' => 1,
                'currentPage' => 1,
                'lastPage' => 2,
                'fromEntry' => 1,
                'toEntry' => 1,
                'totalEntries' => 2,
            ],
        ];

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData)
            ->assertJson($awaitedMeta);
    }
}
