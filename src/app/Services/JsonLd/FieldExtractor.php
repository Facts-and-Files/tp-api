<?php

namespace App\Services\JsonLd;

use App\EDM\LiteralHelper;

class FieldExtractor
{
    protected LiteralResolver $resolver;

    public function __construct(LiteralResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function resolveFieldValue(
        string $field,
        array $jsonLdData,
        array $mappings,
        array $nodeIndex,
    ): ?string {
        $config = $mappings[$field] ?? null;

        if (!$config) {
            return null;
        }

        $separator = $config['separator'] ?? ' | ';
        $graph = $jsonLdData['@graph'] ?? [];
        $graphCollection = $this->ensureIterable($graph);
        $dataCollection  = $this->ensureIterable($jsonLdData);

        $allLiterals = [];

        foreach ($config['paths'] as $path) {
            $type = $path['type'] ?? null;
            $collection = $type ? $graphCollection : $dataCollection;

            $rawValues = $this->extractAllByPathAndType($collection, $path['path'], $type);

            foreach ($rawValues as $raw) {
                $allLiterals = array_merge(
                    $allLiterals, $this->resolver->resolve($raw, $nodeIndex)
                );
            }
        }

        $string = LiteralHelper::toNormalizedString($allLiterals, $separator);

        return $string !== '' ? $string : null;
    }

    private function extractAllByPathAndType(
        array $collection,
        string $path,
        ?string $type = null,
    ): array {
        $values = [];

        foreach ($collection as $item) {
            if (!is_array($item)) continue;

            // type filtering
            if ($type !== null && (!isset($item['@type']) || $item['@type'] !== $type)) {
                continue;
            }

            $value = $this->getValueByDotPath($item, $path);

            if ($value !== null) {
                if (is_array($value)) {
                    $values = array_merge($values, $value);
                } else {
                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    private function getValueByDotPath(array $array, string $path)
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    private function ensureIterable(array $data): array
    {
        return array_is_list($data) ? $data : [$data];
    }
}
