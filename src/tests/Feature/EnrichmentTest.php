<?php

namespace Tests\Feature;

use App\Models\Annotation;
use App\Models\Item;
use App\Models\Transcription;
use Database\Seeders\AnnotationDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Tests\TestCase;

class EnrichmentTest extends TestCase
{
    private string $endpoint = 'enrichments';

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => ItemDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => AnnotationDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => StoryDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => TranscriptionDataSeeder::class]);
    }

    public function test_rejects_unknown_filter_fields(): void
    {
        $response = $this->getJson($this->endpoint . '?foo=bar');

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid data',
            ]);
    }

    public function test_rejects_unknown_transcription_update_fields(): void
    {
        $response = $this->patchJson($this->endpoint . '/transcription/1', [
            'Text' => 'not allowed',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid data',
            ]);
    }

    public function test_requires_europeana_annotation_id_for_transcription_update(): void
    {
        $response = $this->patchJson($this->endpoint . '/transcription/1', []);

        $response->assertStatus(422);
    }

    public function test_rejects_unknown_annotation_update_fields(): void
    {
        $response = $this->patchJson($this->endpoint . '/annotation/1', [
            'ItemId' => 99,
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid data',
            ]);
    }

    public function test_update_transcription_sets_europeana_annotation_id_and_marks_item_as_exported(): void
    {
        $payload = ['EuropeanaAnnotationId' => 12345];

        $response = $this->patch($this->endpoint . '/transcription/1', $payload);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'TranscriptionId' => 1,
                    'EuropeanaAnnotationId' => 12345,
                ],
            ]);

        $this->assertDatabaseHas('Transcription', [
            'TranscriptionId' => 1,
            'EuropeanaAnnotationId' => 12345,
        ]);

        $this->assertDatabaseHas('Item', [
            'ItemId' => 1,
            'Exported' => 1,
        ]);
    }

    public function test_update_annotation_sets_europeana_annotation_id_and_marks_item_as_exported(): void
    {
        $payload = ['EuropeanaAnnotationId' => 67890];

        $response = $this->patch($this->endpoint . '/annotation/1', $payload);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'AnnotationId' => 1,
                    'EuropeanaAnnotationId' => 67890,
                ],
            ]);

        $this->assertDatabaseHas('Annotation', [
            'AnnotationId' => 1,
            'EuropeanaAnnotationId' => 67890,
        ]);

        $this->assertDatabaseHas('Item', [
            'ItemId' => 1,
            'Exported' => 1,
        ]);
    }

    public function test_get_all_enrichments_returns_union_of_annotations_and_transcriptions(): void
    {
        Item::query()->update([
            'TranscriptionStatusId' => 4,
        ]);

        $awaitedSuccess = ['success' => true];

        $response = $this->get($this->endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess);

        // Very lightweight structural check: we get at least two rows
        $this->assertGreaterThanOrEqual(2, count($response->json('data') ?? []));
    }

    public function test_get_all_enrichments_excludes_exported_items_by_default(): void
    {
        // Mark all items as exported
        Item::query()->update(['Exported' => 1]);

        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => []];

        $response = $this->get($this->endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_enrichments_can_include_exported_items_with_flag(): void
    {
        // Mark all items as exported
        Item::query()->update([
            'TranscriptionStatusId' => 4,
            'Exported' => 1,
        ]);

        $awaitedSuccess = ['success' => true];

        $response = $this->get($this->endpoint . '?includeExported=1');

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess);

        $this->assertGreaterThanOrEqual(2, count($response->json('data') ?? []));
    }

    public function test_get_all_enrichments_can_be_filtered_by_storyid(): void
    {
        // Assume your seeder exposes at least two stories with distinct StoryId/RecordId
        $storyId = StoryDataSeeder::$data[0]['RecordId'];

        $response = $this->get($this->endpoint . '?storyId=' . $storyId);

        $response->assertOk()->assertJson(['success' => true]);

        $data = $response->json('data') ?? [];

        // Every returned row should belong to that story
        foreach ($data as $row) {
            $this->assertStringStartsWith($storyId, $row['StoryId']);
        }
    }

    public function test_update_transcription_sets_europeanaannotationid_and_marks_item_exported(): void
    {
        $transcription = Transcription::query()->firstOrFail();

        $createData = [
            'EuropeanaAnnotationId' => 999,
        ];

        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                'TranscriptionId' => $transcription->TranscriptionId,
                'EuropeanaAnnotationId' => 999,
            ],
        ];

        $response = $this->patch(
            $this->endpoint . '/transcription/' . $transcription->TranscriptionId,
            $createData,
        );

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);

        $this->assertDatabaseHas('Transcription', [
            'TranscriptionId' => $transcription->TranscriptionId,
            'EuropeanaAnnotationId' => 999,
        ]);

        $this->assertDatabaseHas('Item', [
            'ItemId' => $transcription->ItemId,
            'Exported' => 1,
        ]);
    }

    public function test_update_annotation_sets_europeanaannotationid_and_marks_item_exported(): void
    {
        $annotation = Annotation::query()->firstOrFail();

        $createData = [
            'EuropeanaAnnotationId' => 888,
        ];

        $response = $this->patch(
            $this->endpoint . '/annotation/' . $annotation->AnnotationId,
            $createData,
        );

        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                'AnnotationId' => $annotation->AnnotationId,
                'EuropeanaAnnotationId' => 888,
            ],
        ];

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);

        $this->assertDatabaseHas('Annotation', [
            'AnnotationId' => $annotation->AnnotationId,
            'EuropeanaAnnotationId' => 888,
        ]);

        $this->assertDatabaseHas('Item', [
            'ItemId' => $annotation->ItemId,
            'Exported' => 1,
        ]);
    }

    public function test_update_transcription_with_missing_europeanaannotationid_returns_422(): void
    {
        $transcription = Transcription::query()->firstOrFail();

        $createData = []; // missing required field

        $response = $this->patch(
            $this->endpoint . '/transcription/' . $transcription->TranscriptionId,
            $createData,
        );

        $response->assertStatus(422);
    }

    public function test_update_transcription_rejects_unknown_fields_with_400(): void
    {
        $transcription = Transcription::query()->firstOrFail();

        $createData = [
            'EuropeanaAnnotationId' => 123,
            'foo' => 'bar',
        ];

        $response = $this->patch(
            $this->endpoint . '/transcription/' . $transcription->TranscriptionId,
            $createData,
        );

        $response->assertStatus(400);
    }

    public function test_unknown_filter_is_rejected_with_400(): void
    {
        $response = $this->get($this->endpoint . '?foo=bar');

        $response->assertStatus(400);
    }
}

