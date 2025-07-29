<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImportStorageService
{
    public function storeImportFile(string $importName, string $recordId, string $content): void
    {
        $recordId = $this->sanitizeRecordId($recordId);
        $path = "{$importName}/{$recordId}.json";

        Storage::disk('imports')->put($path, $content);
    }

    private function sanitizeRecordId(string $recordId): string
    {
        if (str_contains($recordId, '/')) {
            $parts = explode('/', $recordId);
            return $parts[count($parts) - 2] . '_' . end($parts);
        }

        return $recordId;
    }
}
