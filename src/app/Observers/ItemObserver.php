<?php

namespace App\Observers;

use App\Enums\CompletionStatus;
use App\Models\Item;
use App\Models\Story;

class ItemObserver
{
    public function updating(Item $item): void
    {
        $this->applyCompletionStatus($item);
    }

    public function updated(Item $item): void
    {
        if (! $item->wasChanged('CompletionStatusId')) {
            return;
        }

        $this->propagateStatusToStory($item);
    }

    private function propagateStatusToStory(Item $item): void
    {
        $story = Story::find($item->StoryId);

        if ($story === null) {
            return;
        }

        $newStatus = $item->CompletionStatusId;

        if ($this->allItemsInStoryAreComplete($item->StoryId)) {
            $story->CompletionStatusId = CompletionStatus::Completed;
            $story->save();

            return;
        }

        if (
            $newStatus === CompletionStatus::Review &&
            $story->CompletionStatusId->value < CompletionStatus::Review->value
        ) {
            $story->CompletionStatusId = CompletionStatus::Review;
            $story->save();

            return;
        }

        if (
            $newStatus === CompletionStatus::Edit &&
            $story->CompletionStatusId->value < CompletionStatus::Edit->value
        ) {
            $story->CompletionStatusId = CompletionStatus::Edit;
            $story->save();
        }
    }

    private function allItemsInStoryAreComplete(int $storyId): bool
    {
        return Item::where('StoryId', $storyId)
            ->where('CompletionStatusId', '!=', CompletionStatus::Completed->value)
            ->doesntExist();
    }

    private function applyCompletionStatus(Item $item): void
    {
        if ($item->TranscriptionStatusId !== CompletionStatus::Completed) {
            $item->CompletionStatusId = $item->TranscriptionStatusId;

            return;
        }

        if ($this->allStatusesCompleted($item)) {
            if ($item->CompletionStatusId !== CompletionStatus::Completed) {
                $item->CompletionStatusId = CompletionStatus::Completed;
            }

            return;
        }

        $item->CompletionStatusId = CompletionStatus::Review;
    }

    private function allStatusesCompleted(Item $item): bool
    {
        return $item->TranscriptionStatusId === CompletionStatus::Completed
            && $item->DescriptionStatusId === CompletionStatus::Completed
            && $item->LocationStatusId === CompletionStatus::Completed
            && $item->TaggingStatusId === CompletionStatus::Completed;
    }
}
