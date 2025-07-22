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

    private static $storyPlaces = [];

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();

        self::$storyPlaces = [];
        foreach (StoryDataSeeder::$data as $story) {
            self::$storyPlaces[] = [
                'PlaceId' => null,
                'ItemId' => null,
                'Name' => $story['PlaceName'],
                'Latitude' => $story['PlaceLatitude'],
                'Longitude' => $story['PlaceLongitude'],
                'WikidataId' => $story['PlaceLink'],
                'WikidataName' => $story['PlaceComment'],
                'Link' => null,
                'Comment' => null,
                'UserGenerated' => $story['PlaceUserGenerated'],
                'UserId' => $story['PlaceUserId'],
                'PlaceRole' => 'StoryPlace',
                'ItemTitle' => null,
                'StoryId' => $story['StoryId'],
                'StoryTitle' => $story['dc:title'],
                'ProjectId' => $story['ProjectId'],
            ];
        }
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
        $awaitedData['data'] = [...self::$storyPlaces, ...$awaitedData['data']];

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

    public function test_get_all_places_by_role(): void
    {
        $queryParams = '?PlaceRole=StoryPlace';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$storyPlaces];

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

    public function test_get_all_places_by_story_id_and_filter(): void
    {
        $storyId = StoryDataSeeder::$data[0]['StoryId'];
        $endpoint = '/stories/' . $storyId . '/places?PlaceRole=StoryPlace';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => array_filter(
            self::$storyPlaces,
            fn($storyPlace) => $storyPlace['StoryId'] === $storyId
                            && $storyPlace['PlaceRole'] === 'StoryPlace')
        ];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_story_id_and_limited(): void
    {
        $storyId = StoryDataSeeder::$data[0]['StoryId'];
        $endpoint = '/stories/' . $storyId . '/places?limit=1&page=3';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_project_id_and_filter(): void
    {
        $projectId = ProjectDataSeeder::$data[0]['ProjectId'];
        $endpoint = '/projects/' . $projectId . '/places?PlaceRole=StoryPlace';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => array_filter(
            self::$storyPlaces,
            fn($storyPlace) => $storyPlace['ProjectId'] === $projectId
                            && $storyPlace['PlaceRole'] === 'StoryPlace')
        ];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_project_id_and_limited(): void
    {
        $projectId = ProjectDataSeeder::$data[0]['ProjectId'];
        $endpoint = '/projects/' . $projectId . '/places?limit=1&page=4';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_coords(): void
    {
        $latMin = '?latMin=77';
        $latMax = '&latMax=78';
        $lngMin = '&lngMin=21';
        $lngMax = '&lngMax=22';
        $queryParams = $latMin . $latMax . $lngMin . $lngMax;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[0]]];

        $response = $this->get(self::$endpoint . $queryParams);

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
