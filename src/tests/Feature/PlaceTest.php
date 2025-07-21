<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\ProjectDataSeeder;
use Database\Seeders\StoryDataSeeder;
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
        Artisan::call('db:seed', ['--class' => ProjectDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PlaceDataSeeder::class]);
    }

    public function test_get_all_places(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PlaceDataSeeder::$data];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_limited_and_sorted(): void
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

    public function test_get_all_places_by_name(): void
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

    public function test_get_all_places_by_wikidata_id(): void
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

    public function test_get_all_places_by_item_id(): void
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

    public function test_get_all_places_by_item_id_and_limited(): void
    {
        $endpoint = '/items/' . ItemDataSeeder::$data[0]['ItemId'] . '/places?limit=1&page=2';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_story_id(): void
    {
        $endpoint = '/stories/' . StoryDataSeeder::$data[0]['StoryId'] . '/places';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PlaceDataSeeder::$data];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_story_id_and_limited(): void
    {
        $endpoint = '/stories/' . StoryDataSeeder::$data[0]['StoryId'] . '/places?limit=1&page=2';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_project_id(): void
    {
        $endpoint = '/projects/' . ProjectDataSeeder::$data[0]['ProjectId'] . '/places';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PlaceDataSeeder::$data];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_project_id_and_limited(): void
    {
        $endpoint = '/projects/' . ProjectDataSeeder::$data[0]['ProjectId'] . '/places?limit=1&page=2';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_creating_a_place_with_missing_fields_returs_422(): void
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

    public function test_create_a_place(): void
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

    public function test_update_item_status_when_place_is_inserted(): void
    {
        $this->markTestSkipped('must be revisited.');
    }

    public function test_update_a_place(): void
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

    public function test_delete_a_place(): void
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
