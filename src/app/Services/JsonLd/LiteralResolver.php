<?php

namespace App\Services\JsonLd;

use App\EDM\Literal;
use App\EDM\LiteralFactory;

class LiteralResolver
{
    public function resolve(mixed $value, array $nodeIndex): array
    {
        if (is_string($value)) {
            return [new Literal($value)];
        }

        if (is_array($value)) {
            // list of values
            if (array_is_list($value)) {
                return collect($value)
                    ->flatMap(fn($v) => $this->resolve($v, $nodeIndex))
                    ->all();
            }

            // literal object
            if (isset($value['@value'])) {
                return LiteralFactory::fromJsonLd($value);
            }

            // reference
            if (isset($value['@id']) && count($value) === 1) {
                $refId = $value['@id'];
                if (isset($nodeIndex[$refId])) {
                    return $this->extractLabel($nodeIndex[$refId]);
                }
                return [];
            }

            // inline entity
            return $this->extractLabel($value);
        }

        return [];
    }

    private function extractLabel(array $entity): array
    {
        $candidates = ['skos:prefLabel', 'foaf:name', 'dc:title'];

        foreach ($candidates as $prop) {
            if (isset($entity[$prop])) {
                return LiteralFactory::fromJsonLd($entity[$prop]);
            }
        }

        return [];
    }
}
