<?php

namespace App\Listeners;

use App\Enums\CompletionStatus;
use App\Events\PersonInserted;
use App\Models\Item;

class UpdateItemStatusWhenPersonIsInserted
{
    public function handle(PersonInserted $event): void
    {
        $item = Item::find($event->itemId);

        if ($item->CompletionStatusId === CompletionStatus::NotStarted) {
            $item->CompletionStatusId = CompletionStatus::Edit;
        }

        if ($item->TaggingStatusId === CompletionStatus::NotStarted) {
            $item->TaggingStatusId = CompletionStatus::Edit;
        }

        $item->save();
    }
}
