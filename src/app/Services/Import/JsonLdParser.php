<?php

namespace App\Services\Import;

use App\Services\Import\DTO\ParsedJsonLdData;

class JsonLdParser
{
    /** DC / DCterms / EDM fields that map 1-to-1 onto Story columns */
    private const STORY_FIELDS = [
        'dc:title', 'dc:description', 'dc:creator', 'dc:source',
        'dc:contributor', 'dc:publisher', 'dc:coverage', 'dc:date',
        'dc:type', 'dc:relation', 'dc:rights', 'dc:identifier', 'dc:language',
        'edm:landingPage', 'edm:country', 'edm:dataProvider', 'edm:provider',
        'edm:rights', 'edm:begin', 'edm:end', 'edm:year', 'edm:datasetName',
        'edm:isShownAt', 'edm:language', 'edm:agent',
        'dcterms:medium', 'dcterms:provenance', 'dcterms:created',
    ];

    public function parse(array $graph, ?string $iiifUrl = null): ParsedJsonLdData
    {
        $fields = [];
        $manifestUrl = $iiifUrl ?? '';
        $manifestAuthMode = $iiifUrl ? 'token' : 'public';
        $pdfImage = '';
        $externalRecordId = '';
        $recordId = '';

        foreach ($graph as $node) {
            $type = $node['@type'] ?? null;

            // ── Metadata fields ────────────────────────────────────────────
            foreach (self::STORY_FIELDS as $field) {
                if (!isset($node[$field])) {
                    continue;
                }
                $extracted = $this->extractValue($node[$field]);
                if ($extracted === null) {
                    continue;
                }

                if ($field === 'dc:description') {
                    // strip special chars from descriptions
                    $extracted = $this->sanitizeDescription($extracted);
                }

                $fields[$field] = isset($fields[$field])
                    ? $fields[$field] . ' || ' . $extracted
                    : $extracted;
            }

            // ── Inline iiif_url on node ────────────────────────────────────
            if (isset($node['iiif_url']) && $manifestUrl === '') {
                $manifestUrl = $node['iiif_url'];
                $manifestAuthMode = 'token';
            }

            // ── edm:Place ──────────────────────────────────────────────────
            if ($type === 'edm:Place' && !isset($fields['PlaceLatitude'])) {
                $this->extractPlace($node, $fields);
            }

            // ── edm:Agent ─────────────────────────────────────────────────
            if ($type === 'edm:Agent' && isset($node['skos:prefLabel'])) {
                $agentLabel = $this->formatAgent($node);
                $fields['edm:agent'] = isset($fields['edm:agent'])
                    ? $fields['edm:agent'] . ' || ' . $agentLabel
                    : $agentLabel;
            }

            // ── edm:WebResource ───────────────────────────────────────────
            if ($this->isWebResource($node)) {
                if (isset($node['dcterms:isReferencedBy']['@id'])
                    && $this->isValidManifestUrl($node['dcterms:isReferencedBy']['@id'])
                    && $manifestUrl === ''
                ) {
                    $manifestUrl = $node['dcterms:isReferencedBy']['@id'];
                    $manifestAuthMode = 'public';
                }

                $mimeType = $node['ebucore:hasMimeType'] ?? '';
                if (str_contains((string) $mimeType, 'application/pdf')) {
                    $pdfImage = $node['@id'] ?? '';
                }
            }

            // ── edm:ProvidedCHO ───────────────────────────────────────────
            if ($type === 'edm:ProvidedCHO' && isset($node['@id'])) {
                $externalRecordId = $node['@id'];
                $parts    = explode('/', $externalRecordId);
                $recordId = '/' . $parts[count($parts) - 2] . '/' . end($parts);
            }
        }

        return new ParsedJsonLdData(
            fields: $fields,
            manifestUrl: $manifestUrl,
            pdfImage: $pdfImage,
            externalRecordId: $externalRecordId,
            recordId: $recordId,
            manifestAuthMode: $manifestAuthMode,
        );
    }

    private function extractValue(mixed $value): ?string
    {
        if (is_string($value) || is_numeric($value)) {
            return $this->clean((string) $value);
        }

        if (is_array($value)) {
            // JSON-LD object: {"@value": "…"} or {"@id": "…"}
            if (isset($value['@value'])) {
                return $this->clean($value['@value']);
            }
            if (isset($value['@id'])) {
                return $this->clean($value['@id']);
            }

            // JSON-LD array of objects
            $parts = [];
            $englishValue = null;
            foreach ($value as $element) {
                if (!is_array($element)) {
                    $parts[] = $this->clean((string) $element);
                    continue;
                }
                if (isset($element['@language']) && str_contains($element['@language'], 'en')) {
                    $englishValue = $this->clean($element['@value'] ?? $element['@id'] ?? '');
                } elseif (isset($element['@value'])) {
                    $parts[] = $this->clean($element['@value']);
                } elseif (isset($element['@id'])) {
                    $parts[] = $this->clean($element['@id']);
                }
            }
            // prefer the English value if one was found
            return $englishValue ?? (empty($parts) ? null : implode(' || ', $parts));
        }

        return null;
    }

    private function clean(string $value): string
    {
        return trim(str_replace(',', ' |', $value));
    }

    private function sanitizeDescription(string $value): string
    {
        return trim(preg_replace('/["{}\[\]\\\\]/', '', $value));
    }

    private function extractPlace(array $node, array &$fields): void
    {
        $lat = $node['geo:lat'] ?? $node['wgs84_pos:lat'] ?? null;
        $lon = $node['geo:long'] ?? $node['wgs84_pos:long'] ?? null;

        if ($lat !== null && $lon !== null) {
            $fields['PlaceLatitude']  = (string) $lat;
            $fields['PlaceLongitude'] = (string) $lon;
        }

        if (!isset($node['skos:prefLabel'])) {
            return;
        }

        $label = $node['skos:prefLabel'];

        if (is_string($label)) {
            $fields['PlaceName'] = $label;
        } elseif (is_array($label)) {
            if (isset($label['@value'])) {
                $fields['PlaceName'] = $label['@value'];
            } else {
                foreach ($label as $entry) {
                    if (is_array($entry)
                        && isset($entry['@language'])
                        && str_contains($entry['@language'], 'en')
                    ) {
                        $fields['PlaceName'] = $entry['@value'];
                        return;
                    }
                }
            }
        }
    }

    private function formatAgent(array $node): string
    {
        $label = $node['skos:prefLabel'] ?? '';
        $id    = $node['@id'] ?? '';

        $labelStr = is_array($label)
            ? implode(' | ', array_map(fn($e) => is_array($e) ? ($e['@value'] ?? '') : (string) $e, $label))
            : (string) $label;

        return trim($labelStr . ' | ' . $id);
    }

    private function isWebResource(array $node): bool
    {
        $type = $node['@type'] ?? null;
        if (is_string($type)) {
            return $type === 'edm:WebResource';
        }
        if (is_array($type)) {
            return in_array('edm:WebResource', $type, true);
        }
        return false;
    }

    private function isValidManifestUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
