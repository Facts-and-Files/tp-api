<?php

namespace App\Services\ExportCache;

use App\Models\CacheExport;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;

class FileExportCache implements ExportCacheInterface
{
    private const DISK = 'export_cache';

    public function getCached(Item $item, string $format): ?string
    {
        $cache = CacheExport::where('ItemId', $item->ItemId)
            ->where('Format', $format)
            ->first();

        if (!$cache) {
            return null;
        }

        $latestSourceUpdate = $this->getLatestSourceUpdateTimestamp($item);

        if ($cache->SourceUpdatedAt->lt($latestSourceUpdate)) {
            $this->invalidate($cache);

            return null;
        }

        if (!Storage::disk(self::DISK)->exists($cache->FilePath)) {
            $cache->delete();

            return null;
        }

        return Storage::disk(self::DISK)->get($cache->FilePath);
    }

    public function put(Item $item, string $format, string $contents): void
    {
        $filePath = $this->buildFilePath($item, $format);

        Storage::disk(self::DISK)->put($filePath, $contents);

        CacheExport::updateOrCreate(
            [
                'ItemId' => $item->ItemId,
                'Format' => $format,
            ],
            [
                'GeneratedAt' => now(),
                'SourceUpdatedAt' => $this->getLatestSourceUpdateTimestamp($item),
                'FilePath' => $filePath,
            ],
        );
    }

    private function invalidate(CacheExport $cache): void
    {
        if (Storage::disk(self::DISK)->exists($cache->FilePath)) {
            Storage::disk(self::DISK)->delete($cache->FilePath);
        }

        $cache->delete();
    }

    private function buildFilePath(Item $item, string $format): string
    {
        return sprintf(
            '%s/item_%s.%s',
            $format,
            $item->ItemId,
            config("exports.formats.{$format}.extension"),
        );
    }

    private function getLatestSourceUpdateTimestamp(Item $item): string
    {
        $lastUpdated = $item->LastUpdated;
        $transcriptionUpdated = $item->Transcription['Timestamp'];

        if ($lastUpdated && $transcriptionUpdated) {
            return $lastUpdated->max($transcriptionUpdated);
        }

        return $lastUpdated ?? $transcriptionUpdated ?? now();
    }
}
