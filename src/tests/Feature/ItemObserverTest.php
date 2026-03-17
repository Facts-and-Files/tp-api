<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Story;
use App\Enums\CompletionStatus;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Tests\TestCase;

class ItemObserverTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => LanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionLanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemPropertyDataSeeder::class]);
    }

    public function test_completion_status_mirrors_transcription_when_not_complete(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::Edit;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::Edit, $item->CompletionStatusId);
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

    public function test_completion_status_becomes_review_when_transcription_complete_but_others_are_not(): void
    {
        $item = Item::find(ItemDataSeeder::$data[0]['ItemId']);
        $item->TranscriptionStatusId = CompletionStatus::Completed;
        $item->DescriptionStatusId = CompletionStatus::Edit;
        $item->LocationStatusId = CompletionStatus::NotStarted;
        $item->TaggingStatusId = CompletionStatus::NotStarted;
        $item->save();

        $item->refresh();

        $this->assertSame(CompletionStatus::Review, $item->CompletionStatusId);
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
            $item->CompletionStatusId = CompletionStatus::Completed;
            $item->save();
        });

        $lastItem = $items->last();
        $lastItem->CompletionStatusId = CompletionStatus::Edit;
        $lastItem->save();

        $story->refresh();

        $this->assertNotSame(CompletionStatus::Completed, $story->CompletionStatusId);
    }

    public function test_story_completion_status_promotes_to_review_when_item_reaches_review(): void
    {
        $story = Story::find(ItemDataSeeder::$data[0]['StoryId']);
        $story->CompletionStatusId = CompletionStatus::Edit;
        $story->saveQuietly();

        // keep other items non-complete so the complete-branch is skipped
        Item::where('StoryId', $story->StoryId)->skip(1)
            ->each(function (Item $item) {
                $item->saveQuietly();
            });

        $item = Item::where('StoryId', $story->StoryId)->first();
        $item->TranscriptionStatusId = CompletionStatus::Completed; // needed so applyCompletionStatus doesn't override
        $item->DescriptionStatusId = CompletionStatus::Edit;
        $item->LocationStatusId = CompletionStatus::NotStarted;
        $item->TaggingStatusId = CompletionStatus::NotStarted;
        $item->save(); // CompletionStatusId will be set to Review by applyCompletionStatus

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
        $item->save(); // CompletionStatusId will be set to Edit by applyCompletionStatus

        $story->refresh();
        $this->assertSame(CompletionStatus::Edit, $story->CompletionStatusId);
    }

    public function test_story_completion_status_does_not_update_when_item_completion_status_unchanged(): void
    {
        $story = Story::find(ItemDataSeeder::$data[0]['StoryId']);
        $story->CompletionStatusId = CompletionStatus::NotStarted;
        $story->saveQuietly();

        // Save item without changing CompletionStatusId
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
