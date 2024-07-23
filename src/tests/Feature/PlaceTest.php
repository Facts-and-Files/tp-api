<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\PlaceDataSeeder;

class PlaceTest extends TestCase
{
    private static $endpoint = '/places';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PlaceDataSeeder::class]);
    }

    public function testGetAllPlaces(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PlaceDataSeeder::$data];

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
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPlacesByName(): void
    {
        $queryParams = '?Name='. PlaceDataSeeder::$data[1]['Name'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPlacesByWikidataId(): void
    {
        $queryParams = '?WikidataId='. PlaceDataSeeder::$data[1]['WikidataId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPlacesByItemId(): void
    {
        $endpoint = '/items/' . ItemDataSeeder::$data[0]['ItemId'] . '/places';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PlaceDataSeeder::$data];

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
        $placeId = PlaceDataSeeder::$data[1]['PlaceId'];
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
        $placeId = PlaceDataSeeder::$data[1]['PlaceId'];
        $queryParams = '/' . $placeId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PlaceDataSeeder::$data[1]];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
