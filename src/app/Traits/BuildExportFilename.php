<?php

namespace App\Traits;

use DateTimeInterface;

trait BuildExportFilename
{
    protected function buildExportFilename(
        int|string $storyId,
        mixed $itemId,
        ?string $property,
        string $extension,
        ?DateTimeInterface $now = null
    ): string {
        $now ??= now();

        $prefix = config('exports.filename_prefix');

        $parts = [
            $prefix,
            "story-{$storyId}",
        ];

        if ($itemId !== null) {
            $parts[] = "item-{$itemId}";
        }

        if ($property !== null) {
            $parts[] = $property;
        }

        $parts[] = $now->format('Ymd-His');

        return implode('_', $parts) . '.' . ltrim($extension, '.');
    }
}
