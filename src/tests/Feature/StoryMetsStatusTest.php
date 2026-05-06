<?php

namespace Tests\Feature;

use App\Models\Item;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\ProjectDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoryMetsStatusTest extends TestCase
{
    private const STORY_ID = 1;
    private const ENDPOINT = '/stories/' . self::STORY_ID . '/items/export/mets';

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => ProjectDataSeeder::class]);
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

    public function test_status_returns_progress_for_manual_valid_batch(): void
    {
        $batch = Bus::batch([])
            ->name('test batch')
            ->dispatch();

        $this->getJson(self::ENDPOINT . '/status/' . $batch->id)
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'status',
                    'progress' => ['total', 'processed', 'failed', 'percent'],
                    'download_url',
                ],
            ]);
    }

    public function test_status_returns_404_for_unknown_batch(): void
    {
        $this->getJson(self::ENDPOINT . '/status/does-not-exist')
            ->assertNotFound()
            ->assertJson(['success' => false]);
    }

    public function test_status_returns_processing_when_batch_has_pending_jobs(): void
    {
        $batch = $this->dispatchPendingBatch();

        $this->getJson(self::ENDPOINT . "/status/$batch->id")
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.status', 'processing')
            ->assertJsonStructure([
                'data' => [
                    'status',
                    'progress' => ['total', 'processed', 'failed', 'percent'],
                    'download_url',
                ],
            ]);
    }

    public function test_status_progress_reflects_batch_totals(): void
    {
        $batch = $this->dispatchPendingBatch();

        $progress = $this->getJson(self::ENDPOINT . "/status/$batch->id")
            ->json('data.progress');

        $this->assertSame($batch->totalJobs, $progress['total']);
        $this->assertSame(0, $progress['processed']);
        $this->assertSame(0, $progress['failed']);
        $this->assertSame(0, $progress['percent']);
    }

    public function test_status_download_url_is_null_when_processing(): void
    {
        $batch = $this->dispatchPendingBatch();

        $downloadUrl = $this->getJson(self::ENDPOINT . "/status/$batch->id")
            ->json('data.download_url');

        $this->assertNull($downloadUrl);
    }

    public function test_status_returns_ready_when_batch_is_finished(): void
    {
        $batch = $this->dispatchPendingBatch();

        DB::table('job_batches')
            ->where('id', $batch->id)
            ->update(['finished_at' => now()->timestamp]);

        $this->getJson(self::ENDPOINT . "/status/$batch->id")
            ->assertOk()
            ->assertJsonPath('data.status', 'ready')
            ->assertJsonPath('data.download_url', url('/v2/stories/' . self::STORY_ID . '/items/export/mets'));
    }

    public function test_status_returns_cancelled_when_batch_is_cancelled(): void
    {
        $batch = $this->dispatchPendingBatch();

        DB::table('job_batches')
            ->where('id', $batch->id)
            ->update(['cancelled_at' => now()->timestamp]);

        $this->getJson(self::ENDPOINT . "/status/$batch->id")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    private function dispatchPendingBatch(): Batch
    {
        $item = Item::where('StoryId', self::STORY_ID)->first();

        return Bus::batch([new NeverFinishingJob($item)])
            ->name('test-mets-status-batch')
            ->dispatch();
    }
}

class NeverFinishingJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public function handle(): void {}
}
