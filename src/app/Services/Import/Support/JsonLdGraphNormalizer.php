<?php

namespace App\Services\Import\Support;

final class JsonLdGraphNormalizer
{
    public function normalizeGraph(array $graph, array $index): array
    {
        $normalized = [];

        foreach ($graph as $node) {
            if (!is_array($node)) {
                continue;
            }

            $normalized[] = $this->normalizeNode($node, $index);
        }

        return $normalized;
    }

    private function normalizeNode(array $node, array $index, array $visited = []): array
    {
        $nodeId = $node['@id'] ?? null;
        if (is_string($nodeId) && in_array($nodeId, $visited, true)) {
            return $node;
        }

        if (is_string($nodeId)) {
            $visited[] = $nodeId;
        }

        $normalized = [];

        foreach ($node as $key => $value) {
            $normalized[$key] = $this->normalizeValue($value, $index, $visited);
        }

        return $normalized;
    }

    private function normalizeValue(mixed $value, array $index, array $visited = []): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($this->isReferenceObject($value)) {
            $referenceId = $value['@id'];

            if (in_array($referenceId, $visited, true)) {
                return $value;
            }

            $referencedNode = $index[$referenceId] ?? null;
            if ($referencedNode === null) {
                return $value;
            }

            return $this->normalizeNode($referencedNode, $index, $visited);
        }

        if ($this->isAssoc($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item, $index, $visited);
            }

            return $normalized;
        }

        return array_map(
            fn (mixed $item): mixed => $this->normalizeValue($item, $index, $visited),
            $value,
        );
    }

    private function isReferenceObject(array $value): bool
    {
        return isset($value['@id'])
            && is_string($value['@id'])
            && count($value) === 1;
    }

    private function isAssoc(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }
}
