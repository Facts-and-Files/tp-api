<?php

namespace App\Listeners;

use App\Events\PersonInserted;
use App\Models\Item;

class UpdateItemStatusWhenPersonIsInserted
{
    public function handle(PersonInserted $event): void
    {
        $person = $event->person;
        $item = Item::find($person->ItemId);

        if ($item->CompletionStatusId === 1) {
            $item->CompletionStatusId = 2;

            // because of the current MySQL trigger we also need to set the
            // TranscriptionStatusId otherwise it will reset the CompletionStatusId
            // can/should be removed when removing the MySQL trigger
            $item->TranscriptionStatusId = 2;

            $item->save();
        }
    }
}
