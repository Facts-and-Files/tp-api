<?php

namespace App\Services\JsonLd;

class NodeIndexer
{
    private array $nodeIndex = [];

    public function build(array $nodes): void
    {
        if (isset($nodes['@id'], $nodes['@type'])) {
            $this->nodeIndex[$nodes['@id']] = $nodes;
        }

        foreach ($nodes as $node) {
            if (is_array($node)) {
                if (isset($node['@id'], $node['@type'])) {
                    $this->nodeIndex[$node['@id']] = $node;
                }
                $this->build($node);
            }
        }
    }

    public function get(string $id): ?array
    {
        return $this->nodeIndex[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->nodeIndex[$id]);
    }

    public function all(): array
    {
        return $this->nodeIndex;
    }

    public function keys(): array
    {
        return array_keys($this->nodeIndex);
    }
}
