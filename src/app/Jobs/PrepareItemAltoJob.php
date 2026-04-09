<?php

namespace App\Jobs;

use App\Models\Item;
use App\Services\Export\ItemExportManager;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;

final class PrepareItemAltoJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        private readonly Item $item,
    ) {}

    public function handle(ItemExportManager $itemExportManager): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $itemExportManager->export($this->item, 'alto');
    }
}
