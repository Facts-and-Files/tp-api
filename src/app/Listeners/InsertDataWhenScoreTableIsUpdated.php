<?php

namespace App\Listeners;

use App\Events\ScoreTableUpdated;

class InsertDataWhenScoreTableIsUpdated
{
    public function handle(ScoreTableUpdated $event): void
    {
        print_r($event->score);
    }
}
