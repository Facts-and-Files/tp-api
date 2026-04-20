<?php

namespace Tests\Feature;

use App\Enums\CompletionStatus;
use App\Models\Item;
use App\Models\Story;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\ProjectDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ItemObserverTest extends TestCase
{
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
    }

    public function test_completion_status_becomes_complete_when_all_statuses_are_complete(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::Completed;
        $item->DescriptionStatusId = CompletionStatus::Completed;
        $item->LocationStatusId = CompletionStatus::Completed;
        $item->TaggingStatusId = CompletionStatus::Completed;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::Completed, $item->CompletionStatusId);
    }

    public function test_completion_status_becomes_review_when_all_statuses_are_review(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::Review;
        $item->DescriptionStatusId = CompletionStatus::Review;
        $item->LocationStatusId = CompletionStatus::Review;
        $item->TaggingStatusId = CompletionStatus::Review;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::Review, $item->CompletionStatusId);
    }

    public function test_completion_status_becomes_edit_when_all_statuses_are_edit(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::Edit;
        $item->DescriptionStatusId = CompletionStatus::Edit;
        $item->LocationStatusId = CompletionStatus::Edit;
        $item->TaggingStatusId = CompletionStatus::Edit;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::Edit, $item->CompletionStatusId);
    }

    public function test_completion_status_becomes_edit_when_any_status_is_edit(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::Completed;
        $item->DescriptionStatusId = CompletionStatus::Review;
        $item->LocationStatusId = CompletionStatus::Edit;
        $item->TaggingStatusId = CompletionStatus::NotStarted;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::Edit, $item->CompletionStatusId);
    }

    public function test_completion_status_becomes_review_when_any_status_is_review_and_none_are_edit(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::Completed;
        $item->DescriptionStatusId = CompletionStatus::Review;
        $item->LocationStatusId = CompletionStatus::NotStarted;
        $item->TaggingStatusId = CompletionStatus::NotStarted;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::Review, $item->CompletionStatusId);
    }

    public function test_completion_status_becomes_review_when_any_status_is_completed_and_others_are_not_started(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::Completed;
        $item->DescriptionStatusId = CompletionStatus::NotStarted;
        $item->LocationStatusId = CompletionStatus::NotStarted;
        $item->TaggingStatusId = CompletionStatus::NotStarted;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::Review, $item->CompletionStatusId);
    }

    public function test_completion_status_remains_not_started_when_all_statuses_are_not_started(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::NotStarted;
        $item->DescriptionStatusId = CompletionStatus::NotStarted;
        $item->LocationStatusId = CompletionStatus::NotStarted;
        $item->TaggingStatusId = CompletionStatus::NotStarted;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::NotStarted, $item->CompletionStatusId);
    }

    public function test_completion_status_does_not_change_to_complete_if_already_complete(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::Completed;
        $item->DescriptionStatusId = CompletionStatus::Completed;
        $item->LocationStatusId = CompletionStatus::Completed;
        $item->TaggingStatusId = CompletionStatus::Completed;
        $item->CompletionStatusId = CompletionStatus::Completed;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::Completed, $item->CompletionStatusId);
    }

    public function test_story_completion_status_becomes_complete_when_all_items_are_complete(): void
    {
        $story = Story::find(ItemDataSeeder::$data[0]['StoryId']);

        Item::where('StoryId', $story->StoryId)
            ->each(function (Item $item) {
                $item->TranscriptionStatusId = CompletionStatus::Completed;
                $item->DescriptionStatusId = CompletionStatus::Completed;
                $item->LocationStatusId = CompletionStatus::Completed;
                $item->TaggingStatusId = CompletionStatus::Completed;
                $item->save();
            });

        $story->refresh();

        $this->assertSame(CompletionStatus::Completed, $story->CompletionStatusId);
    }

    public function test_story_completion_status_is_not_complete_when_one_item_is_not_complete(): void
    {
        $story = Story::find(ItemDataSeeder::$data[0]['StoryId']);
        $items = Item::where('StoryId', $story->StoryId)->get();

        $items->take($items->count() - 1)->each(function (Item $item) {
            $item->TranscriptionStatusId = CompletionStatus::Completed;
            $item->DescriptionStatusId = CompletionStatus::Completed;
            $item->LocationStatusId = CompletionStatus::Completed;
            $item->TaggingStatusId = CompletionStatus::Completed;
            $item->save();
        });

        $lastItem = $items->last();
        $lastItem->TranscriptionStatusId = CompletionStatus::Edit;
        $lastItem->DescriptionStatusId = CompletionStatus::NotStarted;
        $lastItem->LocationStatusId = CompletionStatus::NotStarted;
        $lastItem->TaggingStatusId = CompletionStatus::NotStarted;
        $lastItem->save();

        $story->refresh();

        $this->assertNotSame(CompletionStatus::Completed, $story->CompletionStatusId);
    }

    public function test_story_completion_status_promotes_to_review_when_item_reaches_review(): void
    {
        $story = Story::find(ItemDataSeeder::$data[0]['StoryId']);
        $story->CompletionStatusId = CompletionStatus::Edit;
        $story->saveQuietly();

        $item = Item::where('StoryId', $story->StoryId)->first();
        $item->TranscriptionStatusId = CompletionStatus::Completed;
        $item->DescriptionStatusId = CompletionStatus::Review;
        $item->LocationStatusId = CompletionStatus::NotStarted;
        $item->TaggingStatusId = CompletionStatus::NotStarted;
        $item->save();

        $story->refresh();

        $this->assertSame(CompletionStatus::Review, $story->CompletionStatusId);
    }

    public function test_story_completion_status_does_not_demote_when_item_reaches_review(): void
    {
        $story = Story::find(ItemDataSeeder::$data[0]['StoryId']);
        $story->CompletionStatusId = CompletionStatus::Completed;
        $story->saveQuietly();

        $item = Item::where('StoryId', $story->StoryId)->first();
        $item->CompletionStatusId = CompletionStatus::Review;
        $item->save();

        $story->refresh();

        $this->assertSame(CompletionStatus::Completed, $story->CompletionStatusId);
    }

    public function test_story_completion_status_promotes_to_edit_when_item_reaches_edit(): void
    {
        $story = Story::find(ItemDataSeeder::$data[0]['StoryId']);
        $story->CompletionStatusId = CompletionStatus::NotStarted;
        $story->saveQuietly();

        $item = Item::where('StoryId', $story->StoryId)->first();
        $item->TranscriptionStatusId = CompletionStatus::Edit;
        $item->DescriptionStatusId = CompletionStatus::NotStarted;
        $item->LocationStatusId = CompletionStatus::NotStarted;
        $item->TaggingStatusId = CompletionStatus::NotStarted;
        $item->save();

        $story->refresh();

        $this->assertSame(CompletionStatus::Edit, $story->CompletionStatusId);
    }

    public function test_story_completion_status_does_not_update_when_item_completion_status_unchanged(): void
    {
        $story = Story::find(ItemDataSeeder::$data[0]['StoryId']);
        $story->CompletionStatusId = CompletionStatus::NotStarted;
        $story->saveQuietly();

        $item = Item::where('StoryId', $story->StoryId)->first();
        $item->Title = 'Updated Title';
        $item->save();

        $story->refresh();

        $this->assertSame(CompletionStatus::NotStarted, $story->CompletionStatusId);
    }

    public function test_story_updated_at_changes_when_item_status_propagates(): void
    {
        $story = Story::find(ItemDataSeeder::$data[0]['StoryId']);
        $originalUpdatedAt = $story->LastUpdated;

        $this->travel(1)->seconds();

        $item = Item::where('StoryId', $story->StoryId)->first();
        $item->TranscriptionStatusId = CompletionStatus::Edit;
        $item->DescriptionStatusId = CompletionStatus::NotStarted;
        $item->LocationStatusId = CompletionStatus::NotStarted;
        $item->TaggingStatusId = CompletionStatus::NotStarted;
        $item->save();

        $story->refresh();

        $this->assertGreaterThan($originalUpdatedAt, $story->LastUpdated);
    }
}
