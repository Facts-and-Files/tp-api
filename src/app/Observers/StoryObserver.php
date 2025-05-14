<?php

namespace App\Observers;

use App\Events\StoryInserted;
use App\Events\StoryDeleted;
use App\Models\Story;


class StoryObserver
{
    public function created(Story $story): void
    {
        event(new StoryInserted($story->DatasetId, $story->StoryId));
    }

    public function deleted(Story $story): void
    {
        event(new StoryDeleted($story->StoryId));
    }
}
