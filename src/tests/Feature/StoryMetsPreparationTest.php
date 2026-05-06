<?php

namespace Tests\Feature;

use App\Http\Controllers\StoryMetsPreparationController;
use App\Mail\MetsReadyMail;
use App\Models\Item;
use App\Models\Story;
use App\Services\ExportCache\FileExportCache;
use App\Services\SendMetsReadyNotification;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class StoryMetsPreparationTest extends TestCase
{
    private const STORY_ID = 1;
    private const ENDPOINT = '/stories/' . self::STORY_ID . '/items/export/mets';
    private const EMAIL = 'dev@example.com';

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
                    'download_url',
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
            ->assertJsonStructure([
                'data' => ['status', 'download_url'],
            ]);
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

    public function test_notification_service_sends_mail_when_email_exists(): void
    {
        Mail::fake();

        $story = Story::findOrFail(self::STORY_ID);

        app(SendMetsReadyNotification::class)->send(
            self::EMAIL,
            $story,
            'ready',
            null,
        );

        Mail::assertQueued(MetsReadyMail::class, function (MetsReadyMail $mail) {
            return $mail->hasTo(self::EMAIL)
                && $mail->envelope()->subject === 'Your requested METS export is ready';
        });
    }

    public function test_prepare_queues_mail_immediately_when_mets_is_already_ready(): void
    {
        Mail::fake();

        $this->warmAltoCacheForStory(self::STORY_ID);

        $this->postJson(self::ENDPOINT, [
            'notificationEmail' => self::EMAIL,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'ready');

        Mail::assertQueued(MetsReadyMail::class, function (MetsReadyMail $mail) {
            return $mail->hasTo(self::EMAIL)
                && $mail->envelope()->subject === 'Your requested METS export is ready';
        });
    }

    public function test_prepare_does_not_queue_mail_when_email_is_missing(): void
    {
        Mail::fake();

        $this->warmAltoCacheForStory(self::STORY_ID);

        $this->postJson(self::ENDPOINT, [])
            ->assertOk();

        Mail::assertNothingQueued();
    }

    public function test_prepare_queues_mail_when_batch_finishes_successfully(): void
    {
        Mail::fake();
        Bus::fake();

        $this->postJson(self::ENDPOINT, [
            'notificationEmail' => self::EMAIL,
        ])
            ->assertAccepted()
            ->assertJsonPath('data.status', 'processing');

        $this->runBatchFinallyCallback([
            'cancelled' => false,
            'hasFailures' => false,
            'finished' => true,
            'processedJobs' => null,
            'failedJobs' => 0,
        ]);

        Mail::assertQueued(MetsReadyMail::class, function (MetsReadyMail $mail) {
            return $mail->hasTo(self::EMAIL)
                && $mail->envelope()->subject === 'Your requested METS export is ready';
        });
    }

    public function test_prepare_queues_mail_when_batch_finishes_with_failures(): void
    {
        Mail::fake();
        Bus::fake();

        $this->postJson(self::ENDPOINT, [
            'notificationEmail' => self::EMAIL,
        ])
            ->assertAccepted()
            ->assertJsonPath('data.status', 'processing');

        $this->runBatchFinallyCallback([
            'cancelled' => false,
            'hasFailures' => true,
            'finished' => true,
            'processedJobs' => null,
            'failedJobs' => 2,
        ]);

        Mail::assertQueued(MetsReadyMail::class, function (MetsReadyMail $mail) {
            return $mail->hasTo(self::EMAIL)
                && $mail->envelope()->subject === 'Your METS export finished with issues';
        });
    }

    public function test_prepare_queues_mail_when_batch_is_cancelled(): void
    {
        Mail::fake();
        Bus::fake();

        $this->postJson(self::ENDPOINT, [
            'notificationEmail' => self::EMAIL,
        ])
            ->assertAccepted()
            ->assertJsonPath('data.status', 'processing');

        $this->runBatchFinallyCallback([
            'cancelled' => true,
            'hasFailures' => false,
            'finished' => false,
            'processedJobs' => 0,
            'failedJobs' => 0,
        ]);

        Mail::assertQueued(MetsReadyMail::class, function (MetsReadyMail $mail) {
            return $mail->hasTo(self::EMAIL)
                && $mail->envelope()->subject === 'Your METS export was cancelled';
        });
    }

    public function test_prepare_does_not_queue_batch_completion_mail_when_email_is_missing(): void
    {
        Mail::fake();
        Bus::fake();

        $this->postJson(self::ENDPOINT, [])
            ->assertAccepted()
            ->assertJsonPath('data.status', 'processing');

        $this->runBatchFinallyCallback([
            'cancelled' => false,
            'hasFailures' => false,
            'finished' => true,
            'processedJobs' => null,
            'failedJobs' => 0,
        ]);

        Mail::assertNothingQueued();
    }

    public function test_show_returns_processing_for_running_batch(): void
    {
        $story = Story::findOrFail(self::STORY_ID);

        $fakeBatch = Mockery::mock(Batch::class);
        $fakeBatch->totalJobs = 10;
        $fakeBatch->failedJobs = 0;
        $fakeBatch->shouldReceive('finished')->twice()->andReturn(false);
        $fakeBatch->shouldReceive('hasFailures')->andReturn(false);
        $fakeBatch->shouldReceive('cancelled')->andReturn(false);
        $fakeBatch->shouldReceive('processedJobs')->times(2)->andReturn(4);

        Bus::shouldReceive('findBatch')
            ->once()
            ->with('running-batch-id')
            ->andReturn($fakeBatch);

        $controller = app(StoryMetsPreparationController::class);
        $response = $controller->show($story->StoryId, 'running-batch-id');

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->getData(true);

        $this->assertSame('processing', $data['data']['status']);
        $this->assertSame(10, $data['data']['progress']['total']);
        $this->assertSame(4, $data['data']['progress']['processed']);
        $this->assertSame(0, $data['data']['progress']['failed']);
        $this->assertSame(40, $data['data']['progress']['percent']);
        $this->assertNull($data['data']['download_url']);
    }

    public function test_show_returns_ready_for_finished_batch_without_failures(): void
    {
        $story = Story::findOrFail(self::STORY_ID);

        $fakeBatch = Mockery::mock(Batch::class);
        $fakeBatch->totalJobs = 10;
        $fakeBatch->failedJobs = 0;
        $fakeBatch->shouldReceive('finished')->twice()->andReturn(true);
        $fakeBatch->shouldReceive('cancelled')->andReturn(false);
        $fakeBatch->shouldReceive('hasFailures')->andReturn(false);
        $fakeBatch->shouldReceive('processedJobs')->times(2)->andReturn(10);

        Bus::shouldReceive('findBatch')
            ->once()
            ->with('ready-batch-id')
            ->andReturn($fakeBatch);

        $controller = app(StoryMetsPreparationController::class);
        $response = $controller->show($story->StoryId, 'ready-batch-id');

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->getData(true);

        $this->assertSame('ready', $data['data']['status']);
        $this->assertSame(10, $data['data']['progress']['total']);
        $this->assertSame(10, $data['data']['progress']['processed']);
        $this->assertSame(0, $data['data']['progress']['failed']);
        $this->assertSame(100, $data['data']['progress']['percent']);
        $this->assertSame(
            url('/v2/stories/' . self::STORY_ID . '/items/export/mets'),
            $data['data']['download_url'],
        );
    }

    public function test_show_returns_finished_with_failures_for_finished_batch_with_failures(): void
    {
        $story = Story::findOrFail(self::STORY_ID);

        $fakeBatch = Mockery::mock(Batch::class);
        $fakeBatch->totalJobs = 10;
        $fakeBatch->failedJobs = 2;
        $fakeBatch->shouldReceive('finished')->twice()->andReturn(true);
        $fakeBatch->shouldReceive('cancelled')->andReturn(false);
        $fakeBatch->shouldReceive('hasFailures')->andReturn(true);
        $fakeBatch->shouldReceive('processedJobs')->times(2)->andReturn(10);

        Bus::shouldReceive('findBatch')
            ->once()
            ->with('failed-batch-id')
            ->andReturn($fakeBatch);

        $controller = app(StoryMetsPreparationController::class);
        $response = $controller->show($story->StoryId, 'failed-batch-id');

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->getData(true);

        $this->assertSame('finished_with_failures', $data['data']['status']);
        $this->assertSame(10, $data['data']['progress']['total']);
        $this->assertSame(10, $data['data']['progress']['processed']);
        $this->assertSame(2, $data['data']['progress']['failed']);
        $this->assertSame(100, $data['data']['progress']['percent']);
        $this->assertSame(
            url('/v2/stories/' . self::STORY_ID . '/items/export/mets'),
            $data['data']['download_url'],
        );
    }

    public function test_show_returns_cancelled_for_cancelled_batch(): void
    {
        $story = Story::findOrFail(self::STORY_ID);

        $fakeBatch = Mockery::mock(Batch::class);
        $fakeBatch->totalJobs = 10;
        $fakeBatch->failedJobs = 0;
        $fakeBatch->shouldReceive('finished')->once()->andReturn(false);
        $fakeBatch->shouldReceive('cancelled')->andReturn(true);
        $fakeBatch->shouldReceive('processedJobs')->times(2)->andReturn(3);

        Bus::shouldReceive('findBatch')
            ->once()
            ->with('cancelled-batch-id')
            ->andReturn($fakeBatch);

        $controller = app(StoryMetsPreparationController::class);
        $response = $controller->show($story->StoryId, 'cancelled-batch-id');

        $this->assertSame(200, $response->getStatusCode());

        $data = $response->getData(true);

        $this->assertSame('cancelled', $data['data']['status']);
        $this->assertSame(10, $data['data']['progress']['total']);
        $this->assertSame(3, $data['data']['progress']['processed']);
        $this->assertSame(0, $data['data']['progress']['failed']);
        $this->assertSame(30, $data['data']['progress']['percent']);
        $this->assertNull($data['data']['download_url']);
    }

    public function test_show_returns_404_when_batch_is_not_found(): void
    {
        $story = Story::findOrFail(self::STORY_ID);

        Bus::shouldReceive('findBatch')
            ->once()
            ->with('missing-batch-id')
            ->andReturn(null);

        $controller = app(StoryMetsPreparationController::class);
        $response = $controller->show($story->StoryId, 'missing-batch-id');

        $this->assertSame(404, $response->getStatusCode());

        $data = $response->getData(true);

        $this->assertFalse($data['success']);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function runBatchFinallyCallback(array $state): void
    {
        Bus::assertBatched(function ($batch) use ($state) {
            $this->assertSame('mets-story-' . self::STORY_ID, $batch->name);

            $callbacks = $batch->finallyCallbacks();
            $this->assertNotEmpty($callbacks);

            $finally = $callbacks[0];

            $totalJobs = $batch->jobs->count();
            $processedJobs = $state['processedJobs'] ?? $totalJobs;

            $fakeBatch = Mockery::mock(Batch::class);
            $fakeBatch->totalJobs = $totalJobs;
            $fakeBatch->failedJobs = $state['failedJobs'];
            $fakeBatch->shouldReceive('cancelled')->andReturn($state['cancelled']);
            $fakeBatch->shouldReceive('hasFailures')->andReturn($state['hasFailures']);
            $fakeBatch->shouldReceive('finished')->andReturn($state['finished']);
            $fakeBatch->shouldReceive('processedJobs')->andReturn($processedJobs);

            $finally($fakeBatch);

            return true;
        });
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
                <alto>
                    <Layout>
                        <Page ID="pixel">
                            <PrintSpace>
                                <TextBlock>
                                    <TextLine>
                                        <String CONTENT="test-image" />
                                    </TextLine>
                                </TextBlock>
                            </PrintSpace>
                        </Page>
                    </Layout>
                </alto>
                XML,
            );
        }
    }
}
