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

    private function graphWithManifest(
        string $externalId = 'http://data.europeana.eu/item/91/BibliographicResource_1',
        string $title = 'Test Story Title',
        string $manifestUrl = 'https://example.com/iiif/manifest'
    ): array {
        return [
            '@graph' => [
                [
                    '@type' => 'edm:ProvidedCHO',
                    '@id' => $externalId,
                    'dc:title' => $title,
                ],
            ],
            'iiif_url' => $manifestUrl,
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

    public function test_import_returns_400_when_manifest_is_missing(): void
    {
        $payload = [
            '@graph' => [
                [
                    '@type' => 'edm:ProvidedCHO',
                    '@id' => 'http://data.europeana.eu/item/91/BibliographicResource_1',
                    'dc:title' => 'Missing Manifest',
                ],
            ],
        ];

        $response = $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $payload,
        );

        $response
            ->assertStatus(400)
            ->assertJson(['success' => false])
            ->assertJsonFragment(['message' => 'Import failed'])
            ->assertJsonFragment(['data' => 'IIIF manifest missing. Import aborted.']);

        $this->assertDatabaseCount('Story', 0);
        $this->assertDatabaseCount('Item', 0);
        Storage::disk('imports')->assertMissing('test/91_BibliographicResource_1.json');
    }

    public function test_import_returns_400_when_manifest_is_unreachable_and_nothing_is_written(): void
    {
        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')
                ->once()
                ->andThrow(new RuntimeException('IIIF manifest not reachable. Status: 404'));
        });

        $payload = $this->graphWithManifest(
            externalId: 'http://data.europeana.eu/item/4/bad_manifest',
            title: 'Bad Manifest',
            manifestUrl: 'https://example.com/iiif/unreachable',
        );

        $response = $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $payload,
        );

        $response
            ->assertStatus(400)
            ->assertJson(['success' => false])
            ->assertJsonFragment(['message' => 'Import failed'])
            ->assertJsonFragment(['data' => 'IIIF manifest not reachable. Status: 404']);

        $this->assertDatabaseMissing('Story', [
            'RecordId' => '/4/bad_manifest',
        ]);
        $this->assertDatabaseCount('Item', 0);
        Storage::disk('imports')->assertMissing('test/4_bad_manifest.json');
    }

    public function test_import_returns_400_when_manifest_has_no_canvases(): void
    {
        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')
                ->once()
                ->andReturn([
                    'canvases' => [],
                    'imageLinks' => [],
                ]);
        });

        $payload = $this->graphWithManifest(
            externalId: 'http://data.europeana.eu/item/5/empty_manifest',
            title: 'Empty Manifest',
        );

        $response = $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $payload,
        );

        $response
            ->assertStatus(400)
            ->assertJson(['success' => false])
            ->assertJsonFragment(['message' => 'Import failed'])
            ->assertJsonFragment(['data' => 'IIIF manifest contains no canvases. Import aborted.']);

        $this->assertDatabaseMissing('Story', [
            'RecordId' => '/5/empty_manifest',
        ]);
        $this->assertDatabaseCount('Item', 0);
        Storage::disk('imports')->assertMissing('test/5_empty_manifest.json');
    }

    public function test_import_with_manifest_creates_story_items_and_raw_import(): void
    {
        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')
                ->once()
                ->andReturn([
                    'canvases' => [
                        ['images' => [['resource' => ['@id' => 'https://example.com/p1.jpg']]]],
                        ['images' => [['resource' => ['@id' => 'https://example.com/p2.jpg']]]],
                    ],
                    'imageLinks' => [
                        'https://example.com/p1.jpg',
                        'https://example.com/p2.jpg',
                    ],
                ]);
        });

        $payload = $this->graphWithManifest(
            externalId: 'http://data.europeana.eu/item/2/manifest_test',
            title: 'Manifest Story',
        );

        $response = $this->post(
            self::$endpoint . '?projectId=1&importName=myimport&datasetId=1',
            $payload,
        );

        $response
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonFragment([
                'ExternalRecordId' => 'http://data.europeana.eu/item/2/manifest_test',
            ]);

        $story = Story::where('RecordId', '/2/manifest_test')->firstOrFail();

        $this->assertDatabaseHas('Story', [
            'StoryId' => $story->StoryId,
            'RecordId' => '/2/manifest_test',
            'ImportName' => 'myimport',
            'ProjectId' => 1,
            'DatasetId' => 1,
        ]);

        $this->assertDatabaseCount('Item', 2);
        $this->assertDatabaseHas('Item', [
            'StoryId' => $story->StoryId,
            'OrderIndex' => 1,
        ]);
        $this->assertDatabaseHas('Item', [
            'StoryId' => $story->StoryId,
            'OrderIndex' => 2,
        ]);

        Storage::disk('imports')->assertExists('myimport/2_manifest_test.json');
    }

    public function test_import_sets_preview_image_from_first_canvas_when_manifest_is_valid(): void
    {
        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')
                ->once()
                ->andReturn([
                    'canvases' => [
                        ['images' => [['resource' => ['@id' => 'https://example.com/cover.jpg']]]],
                    ],
                    'imageLinks' => ['https://example.com/cover.jpg'],
                ]);
        });

        $payload = $this->graphWithManifest(
            externalId: 'http://data.europeana.eu/item/3/preview_test',
            title: 'Preview Test',
        );

        $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $payload,
        )->assertOk();

        $this->assertDatabaseHas('Story', [
            'RecordId' => '/3/preview_test',
            'PreviewImage' => json_encode(['@id' => 'https://example.com/cover.jpg']),
        ]);
    }

    public function test_duplicate_import_updates_existing_story_but_does_not_replace_items_when_manifest_is_valid(): void
    {
        $story = new Story();
        $story->ExternalRecordId = 'http://data.europeana.eu/item/8/upsert_manifest_test';
        $story->RecordId = '/8/upsert_manifest_test';
        $story->ImportName = 'test';
        $story->ProjectId = 1;
        $story->DatasetId = 1;
        $story->PlaceUserGenerated = true;
        $story->dc = [
            'Title' => 'Original Title',
            'Description' => 'Original Description',
        ];
        $story->dcterms = [];
        $story->edm = [];
        $story->save();

        $item1 = new Item();
        $item1->StoryId = $story->StoryId;
        $item1->Title = 'Existing Item 1';
        $item1->ImageLink = json_encode(['@id' => 'https://example.com/existing1.jpg']);
        $item1->OrderIndex = 1;
        $item1->Manifest = 'https://example.com/iiif/manifest';
        $item1->save();

        $item2 = new Item();
        $item2->StoryId = $story->StoryId;
        $item2->Title = 'Existing Item 2';
        $item2->ImageLink = json_encode(['@id' => 'https://example.com/existing2.jpg']);
        $item2->OrderIndex = 2;
        $item2->Manifest = 'https://example.com/iiif/manifest';
        $item2->save();

        $originalStoryId = $story->StoryId;
        $originalItemIds = [$item1->ItemId, $item2->ItemId];

        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')
                ->once()
                ->andReturn([
                    'canvases' => [
                        ['images' => [['resource' => ['@id' => 'https://example.com/new1.jpg']]]],
                        ['images' => [['resource' => ['@id' => 'https://example.com/new2.jpg']]]],
                    ],
                    'imageLinks' => [
                        'https://example.com/new1.jpg',
                        'https://example.com/new2.jpg',
                    ],
                ]);
        });

        $payload = [
            '@graph' => [
                [
                    '@type' => 'edm:ProvidedCHO',
                    '@id' => 'http://data.europeana.eu/item/8/upsert_manifest_test',
                    'dc:title' => 'Updated Title',
                    'dc:description' => 'Updated Description',
                ],
            ],
            'iiif_url' => 'https://example.com/iiif/manifest',
        ];

        $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $payload,
        )->assertOk();

        $story->refresh();

        $currentItemIds = Item::where('StoryId', $story->StoryId)
            ->orderBy('OrderIndex')
            ->pluck('ItemId')
            ->all();

        $this->assertSame($originalStoryId, $story->StoryId);
        $this->assertSame('Updated Title', $story->Dc['Title']);
        $this->assertSame('Updated Description', $story->Dc['Description']);

        $this->assertDatabaseCount('Story', 1);
        $this->assertDatabaseCount('Item', 2);
        $this->assertSame($originalItemIds, $currentItemIds);

        $this->assertDatabaseHas('Item', [
            'ItemId' => $item1->ItemId,
            'StoryId' => $story->StoryId,
            'Title' => 'Existing Item 1',
            'OrderIndex' => 1,
        ]);

        $this->assertDatabaseHas('Item', [
            'ItemId' => $item2->ItemId,
            'StoryId' => $story->StoryId,
            'Title' => 'Existing Item 2',
            'OrderIndex' => 2,
        ]);
    }

    public function test_failed_manifest_check_does_not_update_existing_story(): void
    {
        $story = new Story();
        $story->RecordId = '/7/existing_story';
        $story->ExternalRecordId = 'http://data.europeana.eu/item/7/existing_story';
        $story->ProjectId = 1;
        $story->DatasetId = 1;
        $story->ImportName = 'test';
        $story->dc = ['Title' => 'Original Title'];
        $story->save();

        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')
                ->once()
                ->andThrow(new RuntimeException('IIIF manifest not reachable. Status: 404'));
        });

        $payload = [
            '@graph' => [
                [
                    '@type' => 'edm:ProvidedCHO',
                    '@id' => 'http://data.europeana.eu/item/7/existing_story',
                    'dc:title' => 'Changed Title',
                ],
            ],
            'iiif_url' => 'https://example.com/iiif/bad',
        ];

        $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $payload,
        )
            ->assertStatus(400)
            ->assertJsonFragment(['data' => 'IIIF manifest not reachable. Status: 404']);

        $story->refresh();

        $this->assertSame('Original Title', $story->Dc['Title']);
        $this->assertDatabaseCount('Story', 1);
    }

    public function test_import_full_edm_graph_builds_story_from_all_relevant_nodes(): void
    {
        $payload = json_decode(file_get_contents(base_path('tests/Fixtures/edm_full_test_1.json')), true);

        $this->mock(IiifManifestClient::class, function ($mock) {
            $mock->shouldReceive('fetch')
                ->once()
                ->andReturn([
                    'canvases' => [
                        ['images' => [['resource' => ['@id' => 'https://example.org/image/test_1.jpg']]]],
                    ],
                    'imageLinks' => ['https://example.org/image/test_1.jpg'],
                ]);
        });

        $this->post(
            self::$endpoint . '?projectId=1&importName=test&datasetId=1',
            $payload,
        )->assertOk();

        $story = Story::where('RecordId', '/item/test_1')->firstOrFail();

        $this->assertSame('http://example.org/item/test_1', $story->ExternalRecordId);
        $this->assertSame('Test Item Full Title || Alternative Title', $story->Dc['Title']);
        $this->assertSame('Test description of item 1.', $story->Dc['Description']);
        $this->assertSame('TEST_DATASET_1', $story->Edm['DatasetName']);
        $this->assertSame('https://example.org/item/test_1', $story->Edm['LandingPage']);
        $this->assertSame('Testland', $story->Edm['Country']);
        $this->assertSame('Test Provider', $story->Edm['DataProvider']);
        $this->assertSame('Test Library', $story->Edm['Provider']);
        $this->assertSame('https://provider.example.org/item/test_1', $story->Edm['IsShownAt']);
        $this->assertSame('http://creativecommons.org/licenses/by/4.0/', $story->Edm['Rights']);
    }
}
