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
        ]
    ];

    public function boot(): void
    {
        //
    }
}
