<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\ScoreTableUpdated::class => [
            \App\Listeners\InsertDataWhenScoreTableIsUpdated::class
        ],
        \App\Events\PersonInserted::class => [
            \App\Listeners\UpdateItemStatusWhenPersonIsInserted::class
        ],
        \App\Events\PlaceInserted::class => [
            \App\Listeners\UpdateItemStatusWhenPlaceIsInserted::class
        ],
        \App\Events\StoryInserted::class => [
            \App\Listeners\UpdateStoryCampaignWhenStoryIsInserted::class
        ],
    ];

    public function boot(): void
    {
        //
    }
}
