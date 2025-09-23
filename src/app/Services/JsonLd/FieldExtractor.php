<?php

namespace App\Services\JsonLd;

use App\EDM\LiteralFactory;
use App\EDM\LiteralHelper;
use Illuminate\Support\Collection;

class FieldExtractor
{
    public function resolveFieldValue(string $field, array $jsonLdData, array $mappings): ?string
    {
        $config = $mappings[$field] ?? null;
        if (!$config) return null;

        $dataCollection  = collect($jsonLdData);
        $graphCollection = collect(data_get($dataCollection, '@graph', $dataCollection));

        foreach ($config['paths'] as $path) {
            if ($raw = $this->extractByPathAndType($dataCollection, $path['path'])) {
                return $this->normalize($raw);
            }

            if (!empty($path['type'])) {
                if ($raw = $this->extractByPathAndType($graphCollection, $path['path'], $path['type'])) {
                    return $this->normalize($raw);
                }
            }
        }

        return null;
    }

    private function normalize(mixed $raw): ?string
    {
        $literals = LiteralFactory::fromJsonLd($raw);
        return LiteralHelper::toNormalizedString($literals) ?: null;
    }

    private function extractByPathAndType(Collection $collection, string $path, ?string $type = null): mixed
    {
        if (!$type) {
            return data_get($collection, $path);
        }

        return $collection
            ->filter(fn($item) => isset($item['@type']) && $item['@type'] === $type)
            ->map(fn($item) => data_get($item, $path))
            ->first();
    }
}
