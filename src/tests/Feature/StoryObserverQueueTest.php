<?php

namespace Tests\Feature;

use App\Models\Story;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StoryObserverQueueTest extends TestCase
{
    public function test_inserting_a_story_dispatches_observer_job_to_queue(): void
    {
        Queue::fake();
        $data = [
            'RecordId' => 'test',
            'ExternalRecordId' => 'test',
            'ProjectId' => 6,
            'dc:title' => 'test-title',
        ];

        $story = Story::create($data);

        Queue::assertPushed(
            CallQueuedListener::class,
            function (CallQueuedListener $job) use ($story) {
                return $job->class === 'App\Observers\StoryObserver' &&
                       $job->method === 'created' &&
                       isset($job->data[0]) && $job->data[0]->is($story);
            }
        );
    }

    public function test_deleting_a_story_dispatches_observer_job_to_queue(): void
    {
        Queue::fake();
        $data = [
            'RecordId' => 'test',
            'ExternalRecordId' => 'test',
            'ProjectId' => 6,
            'dc:title' => 'test-title',
        ];

        $story = Story::create($data);
        $story->delete();

        Queue::assertPushed(
            CallQueuedListener::class,
            function (CallQueuedListener $job) use ($story) {
                return $job->class === 'App\Observers\StoryObserver' &&
                       $job->method === 'deleted' &&
                       isset($job->data[0]) && $job->data[0]->is($story);
            }
        );
    }
}
