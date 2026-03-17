<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Story;
use Database\Seeders\CampaignDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportTest extends TestCase
{
    private static string $endpoint = '/import';

    private static array $storyDefaults = [
        'RecordId' => 'RecordId1',
        'Manifest'  => 'http://example.com/manifest/Record1.json',
        'ProjectId' => 1,
        'DatasetId' => 1,
        'Public' => 1,
        'Dc' => ['Title' => 'TestTitle1'],
        'Dcterms' => [],
        'Edm' => [],
    ];

    private static array $itemDefaults = [
        [
            'ProjectItemId' => 'ExternalId1',
            'Title' => 'Item 1',
            'ImageLink' => 'http://img.example.com/1.jpg',
            'OrderIndex' => 1,
        ],
        [
            'ProjectItemId' => 'ExternalId2',
            'Title' => 'Item 2',
            'ImageLink' => 'http://img.example.com/2.jpg',
            'OrderIndex' => 2,
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        ProjectTest::populateTable();
        DatasetTest::populateTable();
        Artisan::call('db:seed', ['--class' => CampaignDataSeeder::class]);
    }

    private function makeImport(array $storyOverrides = [], ?array $items = null): array
    {
        return [
            'Story' => array_merge(self::$storyDefaults, $storyOverrides),
            'Items' => $items ?? self::$itemDefaults,
        ];
    }

    public function test_import_two_stories_returns_200(): void
    {
        $payload = [
            $this->makeImport([
                'RecordId' => 'RecordId1',
                'Dc' => ['Title' => 'Title1'],
            ]),
            $this->makeImport([
                'RecordId' => 'RecordId2',
                'Dc' => ['Title' => 'Title2'],
            ]),
        ];

        $response = $this->post(self::$endpoint, $payload);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(2, 'data');
    }

    public function test_import_persists_stories_and_items_to_database(): void
    {
        $payload = [$this->makeImport()];

        $response = $this->post(self::$endpoint, $payload);

        $response->assertOk();
        $this->assertDatabaseCount('Story', 1);
        $this->assertDatabaseHas('Story', ['RecordId' => 'RecordId1']);
        $this->assertDatabaseCount('Item', count(self::$itemDefaults));
    }

    public function test_story_with_no_items_imports_successfully(): void
    {
        $payload = [$this->makeImport([], [])];

        $response = $this->post(self::$endpoint, $payload);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('Story', 1);
        $this->assertDatabaseCount('Item', 0);
    }

    public function test_story_is_assigned_to_campaign(): void
    {
        $payload = [$this->makeImport()];

        $response = $this->post(self::$endpoint, $payload);

        $response->assertOk();

        $story = Story::where('RecordId', 'RecordId1')->firstOrFail();
        $campaigns = Campaign::where('DatasetId', $story->DatasetId)->get();
        $this->assertNotEmpty($campaigns);

        $linked = $campaigns->contains(
            fn($campaign) => $campaign->stories()->whereKey($story->StoryId)->exists(),
        );
        $this->assertTrue($linked, 'Story was not linked to any campaign');
    }

    public function test_empty_payload_returns_400(): void
    {
        $this->post(self::$endpoint, [])
            ->assertStatus(400);
    }

    public function test_non_list_payload_returns_400(): void
    {
        $this->post(self::$endpoint, $this->makeImport())
            ->assertStatus(400);
    }

    public function test_missing_dc_title_returns_error(): void
    {
        $payload = [$this->makeImport(['Dc' => ['Title' => null]])];

        $response = $this->post(self::$endpoint, $payload);

        $response
            ->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('Story', 0);
    }

    public function test_missing_record_id_returns_error(): void
    {
        $payload = [$this->makeImport(['RecordId' => null])];

        $response = $this->post(self::$endpoint, $payload);

        $response
            ->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_invalid_project_id_returns_error(): void
    {
        $payload = [$this->makeImport(['ProjectId' => 99999])];

        $response = $this->post(self::$endpoint, $payload);

        $response
            ->assertStatus(400)
            ->assertJsonPath('data.0.error.ProjectId.0', __('ProjectId does not exist'));

        $this->assertDatabaseCount('Story', 0);
    }

    public function test_invalid_dataset_id_returns_error(): void
    {
        $payload = [$this->makeImport(['DatasetId' => 99999])];

        $response = $this->post(self::$endpoint, $payload);

        $response
            ->assertStatus(400)
            ->assertJsonPath('data.0.error.DatasetId.0', __('DatasetId does not exist'));

        $this->assertDatabaseCount('Story', 0);
    }

    public function test_all_stories_fail_returns_400(): void
    {
        $payload = [
            $this->makeImport(['Dc' => ['Title' => null]]),
            $this->makeImport(['RecordId' => null]),
        ];

        $response = $this->post(self::$endpoint, $payload);

        $response
            ->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('Story', 0);
    }

    public function test_missing_item_image_link_rolls_back_story(): void
    {
        $items = [
            [
                'ProjectItemId' => 'Ext1',
                'Title' => 'Item 1',
                'ImageLink' => null,
                'OrderIndex' => 1],
        ];
        $payload = [$this->makeImport([], $items)];

        $response = $this->post(self::$endpoint, $payload);

        $response
            ->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('Story', 0);
        $this->assertDatabaseCount('Item', 0);
    }

    public function test_missing_item_title_rolls_back_story(): void
    {
        $items = [
            [
                'ProjectItemId' => 'Ext1',
                'Title' => null,
                'ImageLink' => 'http://img.example.com/1.jpg',
                'OrderIndex' => 1,
            ],
        ];
        $payload = [$this->makeImport([], $items)];

        $response = $this->post(self::$endpoint, $payload);

        $response->assertStatus(400);

        $this->assertDatabaseCount('Story', 0);
        $this->assertDatabaseCount('Item', 0);
    }

    public function test_partial_import_one_story_fails_returns_207(): void
    {
        $payload = [
            $this->makeImport([
                'RecordId' => 'RecordId1',
                'ExternalRecordId' => 'ExtId1',
                'Dc' => ['Title' => 'Title1'],
            ]),
            $this->makeImport([
                'RecordId' => 'RecordId2',
                'ExternalRecordId' => 'ExtId2',
                'Dc' => ['Title' => null],
            ]),
        ];

        $response = $this->post(self::$endpoint, $payload);

        $response
            ->assertStatus(207)
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'error');

        $this->assertDatabaseCount('Story', 1);
    }

    public function test_partial_import_one_story_has_invalid_project_id_returns_207(): void
    {
        $payload = [
            $this->makeImport([
                'RecordId' => 'RecordId1',
                'ExternalRecordId' => 'ExtId1',
            ]),
            $this->makeImport([
                'RecordId' => 'RecordId2',
                'ExternalRecordId' => 'ExtId2',
                'ProjectId' => 99999,
            ]),
        ];

        $response = $this->post(self::$endpoint, $payload);

        $response
            ->assertStatus(207)
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'error');

        $this->assertDatabaseCount('Story', 1);
    }
}
