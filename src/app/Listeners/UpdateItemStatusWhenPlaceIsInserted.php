<?php

namespace App\Listeners;

use App\Enums\CompletionStatus;
use App\Events\PlaceInserted;
use App\Models\Item;

class UpdateItemStatusWhenPlaceIsInserted
{
    public function handle(PlaceInserted $event): void
    {
        $item = Item::find($event->itemId);

        if ($item->CompletionStatusId === CompletionStatus::NotStarted) {
            $item->CompletionStatusId = CompletionStatus::Edit;
        }

        if ($item->LocationStatusId === CompletionStatus::NotStarted) {
            $item->LocationStatusId = CompletionStatus::Edit;
        }

        $item->save();
    }
}
