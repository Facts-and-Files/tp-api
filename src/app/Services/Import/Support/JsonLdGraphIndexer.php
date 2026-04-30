<?php

namespace App\Services\Import\Support;

final class JsonLdGraphIndexer
{
    public function build(array $graph): array
    {
        $index = [];
        $this->collect($graph, $index);

        return $index;
    }

    private function collect(mixed $value, array &$index): void
    {
        if (!is_array($value)) {
            return;
        }

        if ($this->isNode($value)) {
            $index[$value['@id']] = $value;
        }

        foreach ($value as $item) {
            $this->collect($item, $index);
        }
    }

    private function isNode(array $value): bool
    {
        return isset($value['@id']) && is_string($value['@id']) && $value['@id'] !== '';
    }
}
