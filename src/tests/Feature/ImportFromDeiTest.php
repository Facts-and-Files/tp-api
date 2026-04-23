<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Story;
use App\Services\Import\IiifManifestClient;
use Database\Seeders\CampaignDataSeeder;
use Database\Seeders\DatasetDataSeeder;
use Database\Seeders\ProjectDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ImportFromDeiTest extends TestCase
{
    private static $endpoint = '/import/dei';

    private static $secondCallEndpoint = '/v2/import/dei';

    private function minimalGraph(string $externalId = 'http://data.europeana.eu/item/91/BibliographicResource_1'): array
    {
        return [
            '@graph' => [
                [
                    '@type'    => 'edm:ProvidedCHO',
                    '@id'      => $externalId,
                    'dc:title' => 'Test Story Title',
                ],
                [
                    '@type' => 'edm:WebResource',
                    '@id'   => 'https://example.com/image.jpg',
                ],
            ],
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => CampaignDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ProjectDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => DatasetDataSeeder::class]);

        Storage::fake('imports');
    }

    // validation

    public function test_missing_graph_returns_422(): void
    {
        $response = $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            ['not_graph' => []],
        );

        $response->assertUnprocessable();
        $response->assertJson(['success' => false]);
    }

    public function test_missing_import_name_query_param_returns_422(): void
    {
        $response = $this->post(
            self::$endpoint . '?projectId=1&datasetId=1',
            $this->minimalGraph(),
        );

        $response->assertUnprocessable();
    }

    public function test_missing_dataset_id_query_param_returns_422(): void
    {
        $response = $this->post(
            self::$endpoint . '?projectId=1&importName=test',
            $this->minimalGraph(),
        );

        $response->assertUnprocessable();
    }

    public function test_missing_project_id_query_param_returns_422(): void
    {
        $response = $this->post(
            self::$endpoint . '?datasetId=1&importName=test',
            $this->minimalGraph(),
        );

        $response->assertUnprocessable();
    }

    public function test_non_existing_project_returns_422(): void
    {
        $response = $this->post(
            self::$endpoint . '?projectId=1000datasetId=1&importName=test',
            $this->minimalGraph(),
        );

        $response->assertUnprocessable();
    }

    public function test_non_existing_dataset_returns_422(): void
    {
        $response = $this->post(
            self::$endpoint . '?projectId=1datasetId=1000&importName=test',
            $this->minimalGraph(),
        );

        $response->assertUnprocessable();
    }

    public function test_empty_graph_array_returns_422(): void
    {
        $response = $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            ['@graph' => []],
        );

        $response->assertUnprocessable();
    }

    // happy path (no manifest)

    public function test_import_creates_story_in_database(): void
    {
        $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $this->minimalGraph(),
        )->assertOk();

        $this->assertDatabaseHas('Story', [
            'RecordId'  => '/91/BibliographicResource_1',
            'ProjectId' => 1,
            'DatasetId' => 1,
            'ImportName' => 'test',
        ]);
    }

    public function test_import_creates_one_item_when_no_manifest(): void
    {
        $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $this->minimalGraph(),
        )->assertOk();

        $story = Story::where('RecordId', '/91/BibliographicResource_1')->firstOrFail();

        $this->assertDatabaseCount('Item', 1);
        $this->assertDatabaseHas('Item', [
            'StoryId'    => $story->StoryId,
            'OrderIndex' => 1,
        ]);
    }

    public function test_import_response_contains_external_record_id(): void
    {
        $response = $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $this->minimalGraph(),
        );

        $response
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonFragment(['ExternalRecordId' => 'http://data.europeana.eu/item/91/BibliographicResource_1']);
    }

    public function test_import_saves_raw_body_to_storage(): void
    {
        $this->post(
            self::$endpoint . '?projectId=1&importName=myimport&datasetId=1',
            $this->minimalGraph(),
        )->assertOk();

        Storage::disk('imports')->assertExists('myimport/91_BibliographicResource_1.json');
    }

    public function test_import_sets_dc_title_on_story(): void
    {
        $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $this->minimalGraph(),
        )->assertOk();

        $story = Story::where('RecordId', '/91/BibliographicResource_1')->firstOrFail();

        $this->assertSame('Test Story Title', $story->Dc['Title']);
    }

    public function test_import_sets_place_user_generated_flag(): void
    {
        $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $this->minimalGraph(),
        )->assertOk();

        $this->assertDatabaseHas('Story', [
            'RecordId'          => '/91/BibliographicResource_1',
            'PlaceUserGenerated' => true,
        ]);
    }

    // upsert behaviour

    public function test_duplicate_import_updates_story_and_does_not_duplicate_it(): void
    {
        $url = self::$endpoint . '?projectId=1&importName=test&datasetId=1';
        $url2 = self::$secondCallEndpoint . '?projectId=1&importName=test&datasetId=1';

        $payload = $this->minimalGraph();

        $this->post($url, $payload)->assertOk();
        $this->post($url2, $payload)->assertOk();

        $this->assertDatabaseCount('Story', 1);
    }

    public function test_duplicate_import_does_not_add_more_items(): void
    {
        $url = self::$endpoint . '?projectId=1&importName=test&datasetId=1';
        $url2 = self::$secondCallEndpoint . '?projectId=1&importName=test&datasetId=1';
        $payload = $this->minimalGraph();

        $this->post($url, $payload)->assertOk();
        $itemCountAfterFirst = Item::count();

        $this->post($url2, $payload)->assertOk();

        $this->assertDatabaseCount('Item', $itemCountAfterFirst);
    }

    // place data

    public function test_import_extracts_place_coordinates(): void
    {
        $url = self::$endpoint . '?projectId=1&importName=test&datasetId=1';

        $payload = [
            '@graph' => [
                [
                    '@type' => 'edm:ProvidedCHO',
                    '@id'   => 'http://data.europeana.eu/item/1/place_test',
                    'dc:title' => 'Place Test',
                ],
                [
                    '@type'    => 'edm:Place',
                    'geo:lat'  => '52.5200',
                    'geo:long' => '13.4050',
                    'skos:prefLabel' => 'Berlin',
                ],
            ],
        ];

        $this->post($url, $payload)->assertOk();

        $this->assertDatabaseHas('Story', [
            'RecordId'      => '/1/place_test',
            'PlaceLatitude' => '52.5200',
            'PlaceLongitude' => '13.4050',
            'PlaceName'     => 'Berlin',
        ]);
    }

    // IIIF manifest

    public function test_import_with_manifest_creates_one_item_per_canvas(): void
    {
        $url = self::$endpoint . '?projectId=1&importName=test&datasetId=1';

        // Stub out the manifest service so we don't need a live IIIF endpoint
        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')->once()->andReturn([
                'canvases'   => [
                    ['images' => [['resource' => ['@id' => 'https://example.com/p1.jpg']]]],
                    ['images' => [['resource' => ['@id' => 'https://example.com/p2.jpg']]]],
                ],
                'imageLinks' => [
                    'https://example.com/p1.jpg',
                    'https://example.com/p2.jpg',
                ],
            ]);
        });

        $payload = [
            '@graph' => [
                [
                    '@type'    => 'edm:ProvidedCHO',
                    '@id'      => 'http://data.europeana.eu/item/2/manifest_test',
                    'dc:title' => 'Manifest Story',
                ],
            ],
            'iiif_url' => 'https://example.com/iiif/manifest',
        ];

        $this->post($url, $payload)->assertOk();

        $story = Story::where('RecordId', '/2/manifest_test')->firstOrFail();

        $this->assertDatabaseCount('Item', 2);
        $this->assertDatabaseHas('Item', ['StoryId' => $story->StoryId, 'OrderIndex' => 1]);
        $this->assertDatabaseHas('Item', ['StoryId' => $story->StoryId, 'OrderIndex' => 2]);
    }

    public function test_import_sets_preview_image_from_first_canvas(): void
    {
        $url = self::$endpoint . '?projectId=1&importName=test&datasetId=1';

        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')->once()->andReturn([
                'canvases'   => [
                    ['images' => [['resource' => ['@id' => 'https://example.com/cover.jpg']]]],
                ],
                'imageLinks' => ['https://example.com/cover.jpg'],
            ]);
        });

        $payload = [
            '@graph' => [
                [
                    '@type'    => 'edm:ProvidedCHO',
                    '@id'      => 'http://data.europeana.eu/item/3/preview_test',
                    'dc:title' => 'Preview Test',
                ],
            ],
            'iiif_url' => 'https://example.com/iiif/manifest',
        ];

        $this->post($url, $payload)->assertOk();

        $this->assertDatabaseHas('Story', [
            'RecordId'     => '/3/preview_test',
            'PreviewImage' => json_encode(['@id' => 'https://example.com/cover.jpg']),
        ]);
    }

    public function test_import_returns_400_when_manifest_is_unreachable(): void
    {
        $url = self::$endpoint . '?projectId=1&importName=test&datasetId=1';

        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')
                ->once()
                ->andThrow(new RuntimeException('IIIF manifest not reachable. Status: 404'));
        });

        $payload = [
            '@graph' => [
                [
                    '@type'    => 'edm:ProvidedCHO',
                    '@id'      => 'http://data.europeana.eu/item/4/bad_manifest',
                    'dc:title' => 'Bad Manifest',
                ],
            ],
            'iiif_url' => 'https://example.com/iiif/unreachable',
        ];

        $response = $this->post($url, $payload);

        $response
            ->assertStatus(400)
            ->assertJson(['success' => false])
            ->assertJsonFragment(['message' => 'Import failed'])
            ->assertJsonFragment(['data' => 'IIIF manifest not reachable. Status: 404']);
    }

    // auth

    public function test_request_without_token_returns_401(): void
    {
        $response = $this->withToken('')->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $this->minimalGraph(),
        );

        $response->assertUnauthorized();
    }
}
