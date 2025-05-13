<?php

namespace App\Observers;

use App\Events\StoryInserted;
use App\Models\Story;


class StoryObserver
{
    public function created(Story $story): void
    {
        event(new StoryInserted($story->DatasetId, $story->StoryId));
    }
}
