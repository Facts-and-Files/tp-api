<?php

namespace App\Listeners;

use App\Events\PlaceInserted;
use App\Models\Item;

class UpdateItemStatusWhenPlaceIsInserted
{
    public function handle(PlaceInserted $event): void
    {
        $item = Item::find($event->itemId);

        if ($item->CompletionStatusId === 1) {
            $item->CompletionStatusId = 2;

            // because of the current MySQL trigger we also need to set the
            // TranscriptionStatusId otherwise it will reset the CompletionStatusId
            // can/should be removed when removing or changing the MySQL trigger
            $item->TranscriptionStatusId = 2;
        }

        if ($item->LocationStatusId === 1) {
            $item->LocationStatusId = 2;
        }

        $item->save();
    }
}
