<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\Storage;

class RawImportStorage
{
    public function store(string $importName, string $recordId, string $rawBody): void
    {
        $safeRecord = str_replace('/', '_', ltrim($recordId, '/'));
        $path = "{$importName}/{$safeRecord}.json";

        Storage::disk('imports')->put($path, $rawBody);
    }
}
