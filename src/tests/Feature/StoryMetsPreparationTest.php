<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Services\ExportCache\FileExportCache;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoryMetsPreparationTest extends TestCase
{
    private const STORY_ID = 1;
    private const ENDPOINT = '/stories/' . self::STORY_ID . '/items/export/mets';

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => LanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionLanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemPropertyDataSeeder::class]);

        Storage::fake('export_cache');
    }

    public function test_preparation_returns_202_when_alto_cache_is_empty(): void
    {
        Bus::fake();

        $this->postJson(self::ENDPOINT)
            ->assertStatus(202)
            ->assertJsonPath('data.status', 'processing')
            ->assertJsonStructure([
                'data' => [
                    'status',
                    'batch_id',
                    'status_url',
                    'totals' => ['pages', 'cached', 'missing'],
                ],
            ]);
    }

    public function test_preparation_dispatches_one_job_per_missing_item(): void
    {
        Bus::fake();

        $this->postJson(self::ENDPOINT);

        Bus::assertBatched(function ($batch) {
            return $batch->jobs->count() > 0;
        });
    }

    public function test_preparation_returns_200_when_all_alto_already_cached(): void
    {
        Bus::fake();
        $this->warmAltoCacheForStory(self::STORY_ID);

        $this->postJson(self::ENDPOINT)
            ->assertOk()
            ->assertJsonPath('data.status', 'ready')
            ->assertJsonStructure(['data' => ['status', 'download_url']]);
    }

    public function test_preparation_does_not_dispatch_jobs_when_all_alto_cached(): void
    {
        Bus::fake();
        $this->warmAltoCacheForStory(self::STORY_ID);

        $this->postJson(self::ENDPOINT);

        Bus::assertNothingBatched();
    }

    public function test_preparation_returns_404_for_unknown_story(): void
    {
        $this->postJson('/stories/999999/items/export/mets')
            ->assertNotFound()
            ->assertJson(['success' => false]);
    }

    private function warmAltoCacheForStory(int $storyId): void
    {
        $cache = app(FileExportCache::class);

        $items = Item::where('StoryId', $storyId)
            ->orderBy('OrderIndex')
            ->get();

        foreach ($items as $item) {
            $cache->put(
                $item,
                'alto',
                <<<XML
    <alto xmlns="http://www.loc.gov/standards/alto/ns-v4#">
      <Description>
        <MeasurementUnit>pixel</MeasurementUnit>
        <sourceImageInformation>
          <fileName>test-image</fileName>
        </sourceImageInformation>
      </Description>
      <Layout>
        <Page WIDTH="1000" HEIGHT="2000">
          <PrintSpace HPOS="0" VPOS="0" WIDTH="1000" HEIGHT="2000"/>
        </Page>
      </Layout>
    </alto>
    XML,
            );
        }
    }
}
