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

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => ItemDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => AnnotationDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => StoryDataSeeder::class]);
        $this->artisan('db:seed', ['--class' => TranscriptionDataSeeder::class]);

        Item::query()->update([
            'TranscriptionStatusId' => 4,
            'Exported' => 0,
        ]);
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

    public function test_unknown_filter_is_rejected_with_400(): void
    {
        $response = $this->getJson($this->endpoint . '?foo=bar');

        $response->assertStatus(400);
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

    public function test_requires_europeana_annotation_id_for_transcription_update(): void
    {
        $response = $this->patchJson($this->endpoint . '/transcription/1', []);

        $response->assertStatus(422);
    }

    public function test_update_transcription_with_missing_europeanaannotationid_returns_422(): void
    {
        $transcription = Transcription::query()->firstOrFail();

        $response = $this->patchJson(
            $this->endpoint . '/transcription/' . $transcription->TranscriptionId,
            [],
        );

        $response->assertStatus(422);
    }

    public function test_update_transcription_rejects_unknown_fields_with_400(): void
    {
        $transcription = Transcription::query()->firstOrFail();

        $response = $this->patchJson(
            $this->endpoint . '/transcription/' . $transcription->TranscriptionId,
            [
                'EuropeanaAnnotationId' => 123,
                'foo' => 'bar',
            ],
        );

        $response->assertStatus(400);
    }

    public function test_update_transcription_sets_europeana_annotation_id_and_marks_item_as_exported(): void
    {
        $transcription = Transcription::query()->firstOrFail();

        $payload = ['EuropeanaAnnotationId' => 12345];

        $response = $this->patchJson($this->endpoint . '/transcription/' . $transcription->TranscriptionId, $payload);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'TranscriptionId' => $transcription->TranscriptionId,
                    'EuropeanaAnnotationId' => 12345,
                ],
            ]);

        $this->assertDatabaseHas('Transcription', [
            'TranscriptionId' => $transcription->TranscriptionId,
            'EuropeanaAnnotationId' => 12345,
        ]);

        $this->assertDatabaseHas('Item', [
            'ItemId' => $transcription->ItemId,
            'Exported' => 1,
        ]);
    }

    public function test_update_annotation_sets_europeana_annotation_id_and_marks_item_as_exported(): void
    {
        $annotation = Annotation::query()->firstOrFail();

        $payload = ['EuropeanaAnnotationId' => 67890];

        $response = $this->patchJson($this->endpoint . '/annotation/' . $annotation->AnnotationId, $payload);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'AnnotationId' => $annotation->AnnotationId,
                    'EuropeanaAnnotationId' => 67890,
                ],
            ]);

        $this->assertDatabaseHas('Annotation', [
            'AnnotationId' => $annotation->AnnotationId,
            'EuropeanaAnnotationId' => 67890,
        ]);

        $this->assertDatabaseHas('Item', [
            'ItemId' => $annotation->ItemId,
            'Exported' => 1,
        ]);
    }

    public function test_get_all_enrichments_returns_union_of_annotations_and_transcriptions(): void
    {
        $response = $this->getJson($this->endpoint);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);

        $data = $response->json('data') ?? [];

        // lightweight structural check: at least one annotation + one transcription row
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_get_all_enrichments_excludes_exported_items_by_default(): void
    {
        Item::query()->update(['Exported' => 1]);

        $response = $this->getJson($this->endpoint);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }

    public function test_include_exported_flag_controls_visibility_of_exported_items(): void
    {
        Item::query()->update([
            'TranscriptionStatusId' => 4,
            'Exported' => 0,
        ]);

        $item = Item::query()->firstOrFail();

        $item->update(['Exported' => 1]);

        // by default, exported items should be excluded
        $withoutFlag = $this->getJson($this->endpoint);

        $withoutFlag
            ->assertOk()
            ->assertJson(['success' => true]);

        $withoutFlagIds = collect($withoutFlag->json('data') ?? [])
            ->pluck('TranscribathonItemId')
            ->all();

        $this->assertNotContains($item->ItemId, $withoutFlagIds);

        // with includeExported=1, the same item should be included
        $withFlag = $this->getJson('/v2/' . $this->endpoint . '?includeExported=1');

        $withFlag
            ->assertOk()
            ->assertJson(['success' => true]);

        $withFlagIds = collect($withFlag->json('data') ?? [])
            ->pluck('TranscribathonItemId')
            ->all();

        $this->assertContains($item->ItemId, $withFlagIds);
    }

    public function test_get_all_enrichments_can_be_filtered_by_storyid(): void
    {
        $storyId = StoryDataSeeder::$data[0]['RecordId'];

        $response = $this->getJson($this->endpoint . '?storyId=' . $storyId);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);

        $data = $response->json('data') ?? [];

        foreach ($data as $row) {
            $this->assertStringStartsWith($storyId, $row['StoryId']);
        }
    }
}
