<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoryDeleted
{
    use Dispatchable;
    use SerializesModels;

    public $storyId;

    public function __construct(int $storyId)
    {
        $this->storyId = $storyId;
    }
}
