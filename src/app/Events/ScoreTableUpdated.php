<?php

namespace App\Events;

use App\Models\Score;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreTableUpdated
{
    use Dispatchable;
    use SerializesModels;

    public $score;

    public function __construct(Score $score)
    {
        $this->score = $score;
    }
}
