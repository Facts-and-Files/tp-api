<?php

namespace App\Services\Import\Support;

final class JsonLdValueExtractor
{
    public function extractScalar(mixed $value): ?string
    {
        $literals = $this->extractLiterals($value);

        if ($literals === []) {
            return null;
        }

        foreach ($literals as $literal) {
            if ($literal->language !== null && str_contains(mb_strtolower($literal->language), 'en')) {
                return $literal->value;
            }
        }

        return $literals[0]->value;
    }

    public function extractFlattened(mixed $value): ?string
    {
        $literals = $this->extractLiterals($value);

        if ($literals === []) {
            return null;
        }

        return implode(' || ', array_map(
            static fn (Literal $literal): string => $literal->value,
            $literals,
        ));
    }

    public function extractLiterals(mixed $value): array
    {
        if (is_string($value) || is_numeric($value)) {
            return $this->unique([
                new Literal($this->clean((string) $value)),
            ]);
        }

        if (!is_array($value)) {
            return [];
        }

        if (isset($value['@value'])) {
            return $this->unique([
                new Literal(
                    $this->clean((string) $value['@value']),
                    isset($value['@language']) ? (string) $value['@language'] : null,
                ),
            ]);
        }

        foreach (['rdf:value', 'skos:prefLabel', 'dc:title', 'rdfs:label', '@id'] as $field) {
            if (!isset($value[$field])) {
                continue;
            }

            $resolved = $this->extractLiterals($value[$field]);
            if ($resolved !== []) {
                return $resolved;
            }
        }

        $literals = [];

        foreach ($value as $element) {
            if (is_string($element) || is_numeric($element)) {
                $literals[] = new Literal($this->clean((string) $element));
                continue;
            }

            if (!is_array($element)) {
                continue;
            }

            if (isset($element['@value'])) {
                $literals[] = new Literal(
                    $this->clean((string) $element['@value']),
                    isset($element['@language']) ? (string) $element['@language'] : null,
                );
                continue;
            }

            foreach ($this->extractLiterals($element) as $literal) {
                $literals[] = $literal;
            }
        }

        return $this->unique($literals);
    }

    public function extractFirstUrl(mixed $value): ?string
    {
        if (is_string($value) && $this->isValidUrl($value)) {
            return $value;
        }

        if (!is_array($value)) {
            return null;
        }

        if (isset($value['@id']) && is_string($value['@id']) && $this->isValidUrl($value['@id'])) {
            return $value['@id'];
        }

        if (isset($value['@value']) && is_string($value['@value']) && $this->isValidUrl($value['@value'])) {
            return $value['@value'];
        }

        foreach ($value as $item) {
            $url = $this->extractFirstUrl($item);
            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    public function sanitizeDescription(string $value): string
    {
        return trim(str_replace(['"', '{', '}', '[', ']', '\\'], '', $value));
    }

    public function clean(string $value): string
    {
        return trim(str_replace(',', ' |', $value));
    }

    private function unique(array $literals): array
    {
        $unique = [];

        foreach ($literals as $literal) {
            $normalized = trim($literal->value);

            if ($normalized === '') {
                continue;
            }

            $unique[$literal->identity()] = new Literal($normalized, $literal->language);
        }

        return array_values($unique);
    }

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
