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
        if (!$item->wasChanged('CompletionStatusId')) {
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
        $statuses = [
            $item->TranscriptionStatusId,
            $item->DescriptionStatusId,
            $item->LocationStatusId,
            $item->TaggingStatusId,
        ];

        $allCompleted = true;
        $allReview = true;
        $allEdit = true;

        $hasCompleted = false;
        $hasReview = false;
        $hasEdit = false;

        foreach ($statuses as $status) {
            if ($status !== CompletionStatus::Completed) {
                $allCompleted = false;
            } else {
                $hasCompleted = true;
            }

            if ($status !== CompletionStatus::Review) {
                $allReview = false;
            } else {
                $hasReview = true;
            }

            if ($status !== CompletionStatus::Edit) {
                $allEdit = false;
            } else {
                $hasEdit = true;
            }
        }

        if ($allCompleted) {
            $item->CompletionStatusId = CompletionStatus::Completed;
            return;
        }

        if ($allReview) {
            $item->CompletionStatusId = CompletionStatus::Review;
            return;
        }

        if ($allEdit) {
            $item->CompletionStatusId = CompletionStatus::Edit;
            return;
        }

        // one status is still in edit, so item too
        if ($hasEdit) {
            $item->CompletionStatusId = CompletionStatus::Edit;
            return;
        }

        // one status is still in review (other are complete or not started), so item too
        if ($hasReview) {
            $item->CompletionStatusId = CompletionStatus::Review;
            return;
        }

        // one status is complete (others are not started), so item set to review
        if ($hasCompleted) {
            $item->CompletionStatusId = CompletionStatus::Review;
            return;
        }

        $item->CompletionStatusId = CompletionStatus::NotStarted;
    }
}
