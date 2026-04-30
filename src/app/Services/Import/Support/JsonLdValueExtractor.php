<?php

namespace App\Services\Import\Support;

final class JsonLdValueExtractor
{
    public function extractScalar(mixed $value): ?string
    {
        if (is_string($value) || is_numeric($value)) {
            return $this->clean((string) $value);
        }

        if (!is_array($value)) {
            return null;
        }

        if (isset($value['@value'])) {
            return $this->clean((string) $value['@value']);
        }

        foreach (['rdf:value', 'skos:prefLabel', 'dc:title', 'rdfs:label', '@id'] as $field) {
            if (!isset($value[$field])) {
                continue;
            }

            $resolved = $this->extractScalar($value[$field]);
            if ($resolved !== null && $resolved !== '') {
                return $resolved;
            }
        }

        $parts = [];
        $englishValue = null;

        foreach ($value as $element) {
            if (!is_array($element)) {
                $parts[] = $this->clean((string) $element);
                continue;
            }

            if (isset($element['@language']) && str_contains((string) $element['@language'], 'en')) {
                $englishValue = $this->clean((string) ($element['@value'] ?? $this->extractScalar($element) ?? ''));
                continue;
            }

            $resolved = $this->extractScalar($element);
            if ($resolved !== null && $resolved !== '') {
                $parts[] = $resolved;
            }
        }

        return $englishValue ?? (empty($parts) ? null : implode(' || ', $parts));
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

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
