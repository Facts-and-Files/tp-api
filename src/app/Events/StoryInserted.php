<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoryInserted
{
    use Dispatchable, SerializesModels;

    public $storyId;
    public $datasetId;

    public function __construct(int $datasetId, int $storyId)
    {
        $this->storyId = $storyId;
        $this->datasetId = $datasetId;
    }
}
