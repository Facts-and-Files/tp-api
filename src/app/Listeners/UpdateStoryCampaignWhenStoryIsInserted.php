<?php

namespace App\Listeners;

use App\Events\StoryInserted;
use App\Models\Campaign;

class UpdateStoryCampaignWhenStoryIsInserted
{
    public function handle(StoryInserted $event): void
    {
        $campaigns = Campaign::where('DatasetId', $event->datasetId)->get();

        if ($campaigns->isEmpty()) {
            return;
        }

        foreach ($campaigns as $campaign) {
            $campaign->stories()->syncWithoutDetaching([$event->storyId]);
        }
    }
}
