<?php

namespace App\Services\ExportCache;

use App\Models\Item;

interface ExportCacheInterface
{
    public function getCached(Item $item, string $format): ?string;
    public function put(Item $item, string $format, string $contents): void;
}
