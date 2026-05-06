<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Enums\CompletionStatus;
use Database\Seeders\DatasetDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\PlaceDataSeeder;
use Database\Seeders\PlaceLinkDataSeeder;
use Database\Seeders\ProjectDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlaceTest extends TestCase
{
    private static $endpoint = '/places';

    private static $storyPlaces = [];

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable(): void
    {
        Artisan::call('db:seed', ['--class' => ProjectDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => DatasetDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PlaceDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PlaceLinkDataSeeder::class]);
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
        $queryParams = '?Name=' . PlaceDataSeeder::$data[1]['Name'];
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
        $queryParams = '?WikidataId=' . PlaceDataSeeder::$data[1]['WikidataId'];
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
        $endpoint = '/items/' . ItemDataSeeder::$data[0]['ItemId'] . '/places';
        $queryParams = '?limit=1&page=2';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_story_id_and_filter(): void
    {
        $storyId = StoryDataSeeder::$data[0]['StoryId'];
        $queryParams = '?PlaceRole=CreationPlace';
        $endpoint = '/stories/' . $storyId . '/places';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[0]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_story_id_and_limited(): void
    {
        $storyId = StoryDataSeeder::$data[0]['StoryId'];
        $endpoint = '/stories/' . $storyId . '/places';
        $queryParams = '?limit=1&page=1';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[0]]];

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_project_id_and_filter(): void
    {
        $projectId = ProjectDataSeeder::$data[0]['ProjectId'];
        $endpoint = '/projects/' . $projectId . '/places';
        $queryParams = '?PlaceRole=CreationPlace';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[0]]];

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_project_id_and_limited(): void
    {
        $projectId = ProjectDataSeeder::$data[0]['ProjectId'];
        $endpoint = '/projects/' . $projectId . '/places';
        $queryParams = '?limit=1&page=2';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get($endpoint . $queryParams);

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

    public function test_show_a_place_with_links(): void
    {
        $placeId = PlaceDataSeeder::$data[0]['PlaceId'];

        $response = $this->get(self::$endpoint . '/' . $placeId);

        $response
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.PlaceId', $placeId)
            ->assertJsonPath('data.Name', PlaceDataSeeder::$data[0]['Name'])
            ->assertJsonPath('data.Links.0.Provider', 'Wikidata')
            ->assertJsonPath('data.Links.1.Provider', 'Wikipedia');
    }

    public function test_creating_a_place_with_missing_fields_returs_422(): void
    {
        $createData = [
            'Name' => 'Test',
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
            'Name' => 'TestStadt 3',
            'ItemId' => 1,
            'Longitude' => 0.0,
            'Latitude' => 0.0,
            'Links' => [
                [
                    'Provider' => 'Wikidata',
                    'Url' => 'https://www.wikidata.org/wiki/Q999',
                ],
                [
                    'Provider' => 'Wikipedia',
                    'Url' => 'https://en.wikipedia.org/wiki/TestStadt_3',
                ],
            ],
        ];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.Name', 'TestStadt 3')
            ->assertJsonPath('data.Links.0.Provider', 'Wikidata')
            ->assertJsonPath('data.Links.0.Url', 'https://www.wikidata.org/wiki/Q999')
            ->assertJsonPath('data.Links.1.Provider', 'Wikipedia')
            ->assertJsonPath('data.Links.1.Url', 'https://en.wikipedia.org/wiki/TestStadt_3');

        $placeId = $response->json('data.PlaceId');

        $this->assertDatabaseHas('PlaceLink', [
            'PlaceId' => $placeId,
            'Provider' => 'Wikidata',
            'Url' => 'https://www.wikidata.org/wiki/Q999',
        ]);

        $this->assertDatabaseHas('PlaceLink', [
            'PlaceId' => $placeId,
            'Provider' => 'Wikipedia',
            'Url' => 'https://en.wikipedia.org/wiki/TestStadt_3',
        ]);
    }

    public function test_create_a_place_with_multiple_links(): void
    {
        $createData = [
            'Name' => 'TestStadt 2',
            'ItemId' => 1,
            'Longitude' => 0.0,
            'Latitude' => 0.0,
            'Links' => [
                [
                    'Provider' => 'Wikidata',
                    'Url' => 'https://www.wikidata.org/wiki/Q42',
                ],
                [
                    'Provider' => 'Wikipedia',
                    'Url' => 'https://en.wikipedia.org/wiki/Douglas_Adams',
                ],
            ],
        ];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.Name', 'TestStadt 2')
            ->assertJsonPath('data.Links.0.Provider', 'Wikidata')
            ->assertJsonPath('data.Links.1.Provider', 'Wikipedia');

        $this->assertDatabaseHas('PlaceLink', [
            'Provider' => 'Wikidata',
            'Url' => 'https://www.wikidata.org/wiki/Q42',
        ]);

        $this->assertDatabaseHas('PlaceLink', [
            'Provider' => 'Wikipedia',
            'Url' => 'https://en.wikipedia.org/wiki/Douglas_Adams',
        ]);
    }

    public function test_update_a_place_and_replace_links(): void
    {
        $placeId = PlaceDataSeeder::$data[0]['PlaceId'];

        // Seed initial links here or create them directly before request

        $updateData = [
            'Links' => [
                [
                    'Provider' => 'Google Maps',
                    'Url' => 'https://maps.google.com/?q=1,1',
                ],
            ],
        ];

        $response = $this->put(self::$endpoint . '/' . $placeId, $updateData);

        $response
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.Links.0.Provider', 'Google Maps');

        $this->assertDatabaseHas('PlaceLink', [
            'PlaceId' => $placeId,
            'Provider' => 'Google Maps',
            'Url' => 'https://maps.google.com/?q=1,1',
        ]);
    }

    public function test_create_a_place_with_invalid_link_url_returns_422(): void
    {
        $createData = [
            'Name' => 'Test',
            'ItemId' => 1,
            'Longitude' => 0.0,
            'Latitude' => 0.0,
            'Links' => [
                [
                    'Provider' => 'Wikidata',
                    'Url' => 'not-a-valid-url',
                ],
            ],
        ];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_update_item_status_when_place_is_inserted(): void
    {
        $itemId = ItemDataSeeder::$data[0]['ItemId'];

        DB::table('Item')
            ->where('ItemId', $itemId)
            ->update([
                'CompletionStatusId' => CompletionStatus::NotStarted,
                'LocationStatusId' => CompletionStatus::NotStarted,
            ]);

        $createData = [
            'Name' => 'Place status test',
            'ItemId' => $itemId,
            'Longitude' => 10.123456,
            'Latitude' => 20.123456,
        ];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('Item', [
            'ItemId' => $itemId,
            'CompletionStatusId' => CompletionStatus::Edit,
            'LocationStatusId' => CompletionStatus::Edit,
        ]);
    }

    public function test_place_insert_does_not_overwrite_existing_item_statuses(): void
    {
        $itemId = ItemDataSeeder::$data[0]['ItemId'];

        DB::table('Item')
            ->where('ItemId', $itemId)
            ->update([
                'CompletionStatusId' => CompletionStatus::Completed,
                'LocationStatusId' => CompletionStatus::Edit,
            ]);

        $response = $this->post(self::$endpoint, [
            'Name' => 'Place status guard test',
            'ItemId' => $itemId,
            'Longitude' => 10.123456,
            'Latitude' => 20.123456,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('Item', [
            'ItemId' => $itemId,
            'CompletionStatusId' => CompletionStatus::Completed,
            'LocationStatusId' => CompletionStatus::Edit,
        ]);
    }
    /* public function test_update_item_status_when_place_is_inserted(): void */
    /* { */
    /*     $this->markTestSkipped('must be revisited.'); */
    /* } */

    public function test_update_a_place(): void
    {
        $placeId = PlaceDataSeeder::$data[1]['PlaceId'];

        $updateData = [
            'Name' => 'Teststadt 4',
            'Links' => [
                [
                    'Provider' => 'Google Maps',
                    'Url' => 'https://maps.google.com/?q=1,1',
                ],
                [
                    'Provider' => 'OpenStreetMap',
                    'Url' => 'https://www.openstreetmap.org/?mlat=1&mlon=1',
                ],
            ],
        ];

        $response = $this->put(self::$endpoint . '/' . $placeId, $updateData);

        $response
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.Name', 'Teststadt 4')
            ->assertJsonPath('data.Links.0.Provider', 'Google Maps')
            ->assertJsonPath('data.Links.1.Provider', 'OpenStreetMap');

        $this->assertDatabaseHas('PlaceLink', [
            'PlaceId' => $placeId,
            'Provider' => 'Google Maps',
            'Url' => 'https://maps.google.com/?q=1,1',
        ]);

        $this->assertDatabaseHas('PlaceLink', [
            'PlaceId' => $placeId,
            'Provider' => 'OpenStreetMap',
            'Url' => 'https://www.openstreetmap.org/?mlat=1&mlon=1',
        ]);

        $this->assertDatabaseMissing('PlaceLink', [
            'PlaceId' => $placeId,
            'Provider' => 'Wikidata',
            'Url' => 'https://www.wikidata.org/wiki/Q778',
        ]);
    }

    public function test_update_a_place_with_empty_links_array_removes_all_links(): void
    {
        $placeId = PlaceDataSeeder::$data[0]['PlaceId'];

        $response = $this->put(self::$endpoint . '/' . $placeId, [
            'Links' => [],
        ]);

        $response
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(0, 'data.Links');

        $this->assertDatabaseMissing('PlaceLink', [
            'PlaceId' => $placeId,
            'Provider' => 'Wikidata',
        ]);

        $this->assertDatabaseMissing('PlaceLink', [
            'PlaceId' => $placeId,
            'Provider' => 'Wikipedia',
        ]);
    }

    public function test_delete_a_place(): void
    {
        $placeId = PlaceDataSeeder::$data[1]['PlaceId'];
        $queryParams = '/' . $placeId;

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('Place', [
            'PlaceId' => $placeId,
        ]);

        $this->assertDatabaseMissing('PlaceLink', [
            'PlaceId' => $placeId,
        ]);
    }

    public function test_get_all_places_by_dataset_id(): void
    {
        $datasetId = StoryDataSeeder::$data[0]['DatasetId'];
        $endpoint  = '/datasets/' . $datasetId . '/places';

        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PlaceDataSeeder::$data];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_dataset_id_and_limited(): void
    {
        $datasetId = StoryDataSeeder::$data[0]['DatasetId'];
        $endpoint  = '/datasets/' . $datasetId . '/places';
        $queryParams = '?limit=1&page=2';

        $awaitedSuccess = ['success' => true];
        $awaitedData    = ['data' => [PlaceDataSeeder::$data[1]]];

        $response = $this->get($endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_dataset_filter(): void
    {
        $datasetId   = StoryDataSeeder::$data[0]['DatasetId'];
        $queryParams = '?DatasetId=' . $datasetId;

        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PlaceDataSeeder::$data];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_dataset_filter_and_role(): void
    {
        $datasetId   = StoryDataSeeder::$data[0]['DatasetId'];
        $queryParams = '?DatasetId=' . $datasetId . '&PlaceRole=CreationPlace';

        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PlaceDataSeeder::$data[0]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_places_by_link_provider(): void
    {
        $placeId = PlaceDataSeeder::$data[0]['PlaceId'];

        $place = Place::find($placeId);
        $place->links()->create([
            'Provider' => 'wikidata.org',
            'Url' => 'https://www.wikidata.org/wiki/Q998856',
        ]);

        $queryParams = '?LinkProvider=wikidata.org';
        $awaitedSuccess = ['success' => true];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJsonFragment([
                'PlaceId' => $placeId,
            ]);

        $response->assertJsonMissing([
            'PlaceId' => PlaceDataSeeder::$data[1]['PlaceId'],
        ]);
    }
}
