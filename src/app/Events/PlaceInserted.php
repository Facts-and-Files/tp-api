<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaceInserted
{
    use Dispatchable, SerializesModels;

    public $itemId;

    public function __construct(int $itemId)
    {
        $this->itemId = $itemId;
    }
}
