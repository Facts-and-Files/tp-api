<?php

namespace App\Listeners;

use App\Events\ScoreTableUpdated;
use App\Models\User;

class InsertDataWhenScoreTableIsUpdated
{
    public function handle(ScoreTableUpdated $event): void
    {
        $score = $event->score;
        $user = User::find($score->UserId);

        if ($user) {
            $teamIds = $user->teams->pluck('TeamId')->toArray();
            $score->teams()->sync($teamIds);
        }
    }
}
