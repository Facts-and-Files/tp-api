<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class PlaceTest extends TestCase
{
    private static $endpoint = '/places';

    private static $tableData = [
        [
            'PlaceId'       => 1,
            'Name'          => 'TestStadt',
            'Latitude'      => 78,
            'Longitude'     => 21,
            'ItemId'        => 1,
            'Link'          => 'link',
            'Zoom'          => 10,
            'Comment'       => 'TestComment',
            'UserGenerated' => true,
            'UserId'        => 1,
            'WikidataName'  => 'Teststadt',
            'WikidataId'    => 'Q777',
            'PlaceRole'     => 'Other'
        ],
        [
            'PlaceId'       => 2,
            'Name'          => 'TestStadt 2',
            'Latitude'      => 78,
            'Longitude'     => 21,
            'ItemId'        => 1,
            'Link'          => 'link',
            'Zoom'          => 10,
            'Comment'       => 'TestComment',
            'UserGenerated' => true,
            'UserId'        => 1,
            'WikidataName'  => 'Teststadt',
            'WikidataId'    => 'Q778',
            'PlaceRole'     => 'Other'
        ]
    ];

    private static $itemData = [
        [
            'ItemId'                => 1,
            'StoryId'               => 1,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocationStatusId'      => 1
        ],
        [
            'ItemId'                => 2,
            'StoryId'               => 1,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocaionStatusId'       => 1
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        DB::table('Item')->insert(self::$itemData);
        DB::table('Place')->insert(self::$tableData);
    }

    public function testGetAllPlaces(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPlacesLimitedAndSorted(): void
    {
        $queryParams = '?limit=1&page=1&orderBy=PlaceId&orderDir=desc';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPlacesByName(): void
    {
        $queryParams = '?Name='. self::$tableData[1]['Name'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPlacesByWikidataId(): void
    {
        $queryParams = '?WikidataId='. self::$tableData[1]['WikidataId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPlacesByItemId(): void
    {
        $endpoint = '/items/' . self::$itemData[0]['ItemId'] . '/places';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetValidationErrorWhenCreateAPlace(): void
    {
        $createData = [
            'Name' => 'Test'
        ];
        $awaitedSuccess = ['success' => false];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertStatus(422)
            ->assertJson($awaitedSuccess);
    }

    public function testGetCreateAPlace(): void
    {
        $createData = [
            'Name'      => 'TestStadt 2',
            'ItemId'    => 1,
            'Longitude' => 0.0,
            'Latitude'  => 0.0
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testUpdateItemStatusWhenPlaceIsInserted(): void
    {
        $this->markTestSkipped('must be revisited.');
    }

    public function testUpdateAPlace(): void
    {
        $updateData = [
            'Name' => 'Teststadt 4'
        ];
        $placeId = self::$tableData[1]['PlaceId'];
        $queryParams = '/' . $placeId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $updateData];

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testDeleteAPlace(): void
    {
        $placeId = self::$tableData[1]['PlaceId'];
        $queryParams = '/' . $placeId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData[1]];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
