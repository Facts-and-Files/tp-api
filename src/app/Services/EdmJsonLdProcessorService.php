<?php

namespace App\Services;

use App\EDM\Literal;
use App\EDM\LiteralFactory;

class EdmJsonLdProcessorService
{
    private const EDM_DATABASE_FIELDS = [
        'dc:title', 'dc:description', 'edm:landingPage', 'dc:creator',
        'dc:source', 'edm:country', 'edm:dataProvider', 'edm:provider',
        'edm:rights', 'edm:begin', 'edm:end', 'dc:contributor', 'edm:year',
        'dc:publisher', 'dc:coverage', 'dc:date', 'dc:type', 'dc:relation',
        'dcterms:medium', 'edm:datasetName', 'edm:isShownAt', 'dc:rights',
        'dc:identifier', 'dc:language', 'edm:language', 'edm:agent',
        'dcterms:provenance', 'dcterms:created',
    ];

    private array $nodeIndex = [];

    public function processJsonLd(array $graphNodes): array
    {
        $processedData = [
            'story' => [],
            'recordId' => '',
            'externalRecordId' => '',
            'storyTitle' => '',
            'manifestUrl' => '',
            'imageLink' => '',
            'pdfImage' => '',
            'imageLinks' => [],
            'placeAdded' => false
        ];

        // first pass: build an index of all nodes by their @id
        $this->buildNodeIndex($graphNodes);

print_r($graphNodes);exit();
        // second pass: process items with reference resolution
        foreach ($graphNodes as $nodes) {
            $this->processJsonLdItem($nodes, $processedData);
        }

        return $processedData;
    }

    private function buildNodeIndex(array $nodes): void
    {
        if (isset($nodes['@id']) && isset($nodes['@type'])) {
            $this->nodeIndex[$nodes['@id']] = $nodes;
        }

        foreach ($nodes as $node) {
            if (is_array($node)) {
                if (isset($node['@id']) && isset($node['@type'])) {
                    $this->nodeIndex[$node['@id']] = $node;
                }

                // recurse into children
                $this->buildNodeIndex($node);
            }
        }
    }

    public function getLiterals(string $entityId, string $property): array
    {
        if (!isset($this->nodeIndex[$entityId][$property])) {
            return [];
        }

        return $this->resolveValue($this->nodeIndex[$entityId][$property]);
    }

    public function resolveProperty(string $property): array
    {
        $literals = [];

        foreach ($this->nodeIndex as $node) {
            if (isset($node[$property])) {
                $literals = array_merge($literals, $this->resolveValue($node[$property]));
            }
        }

        return $literals;
    }

    private function collectLiteralsFromEntity(array $entity): array
    {
        // EDM often uses these labels for contextual entities
        $candidates = ['skos:prefLabel', 'foaf:name', 'dc:title'];

        foreach ($candidates as $prop) {
            if (isset($entity[$prop])) {
                return LiteralFactory::fromJsonLd($entity[$prop]);
            }
        }

        return [];
    }

    public function resolveEntity(string $id): ?array
    {
        return $this->nodeIndex[$id] ?? null;
    }

    private function resolveValue(mixed $value): array
    {
        if (is_string($value)) {
            return [new Literal($value)];
        }

        if (is_array($value)) {
            // list of values
            if (array_is_list($value)) {
                $results = [];
                foreach ($value as $v) {
                    $results = array_merge($results, $this->resolveValue($v));
                }
                return $results;
            }

            // literal object
            if (isset($value['@value'])) {
                return LiteralFactory::fromJsonLd($value);
            }
            // reference to another entity
            if (isset($value['@id']) && count($value) === 1) {
                $refId = $value['@id'];
                if (isset($this->nodeIndex[$refId])) {
                    return $this->extractLabelFromEntity($this->nodeIndex[$refId]);
                }
                return []; // unresolved reference
            }

            // inline entity
            return $this->extractLabelFromEntity($value);
        }

        return [];
    }

    private function extractLabelFromEntity(array $entity): array
    {
        $candidates = ['skos:prefLabel', 'foaf:name', 'dc:title'];

        foreach ($candidates as $prop) {
            if (isset($entity[$prop])) {
                return LiteralFactory::fromJsonLd($entity[$prop]);
            }
        }

        return [];
    }

    private function processJsonLdItem(mixed $node, array &$processedData): void
    {
print_r($node);
        foreach ($node as $key => $value) {

            match ($key) {
                // '@type' => $this->processTypeField($item, $value, $processedData),
                'iiif_url' => $processedData['manifestUrl'] = $value,
                // default => $this->processRegularField($key, $value, $processedData)
            };
        }
    }

    // private function processTypeField(array $item, $value, array &$processedData): void
    // {
    //     $type = is_array($value) ? $value[0] : $value;
    //
    //     switch ($type) {
    //         case 'edm:Place':
    //             $this->processPlace($item, $processedData);
    //             break;
    //         case 'edm:Agent':
    //             $this->processAgent($item, $processedData);
    //             break;
    //         case 'edm:WebResource':
    //             $this->processWebResource($item, $processedData);
    //             break;
    //         case 'edm:ProvidedCHO':
    //             $this->processProvidedCHO($item, $processedData);
    //             break;
    //     }
    // }
    //
    // private function processRegularField(string $key, $value, array &$processedData): void
    // {
    //     if (!in_array($key, self::SUPPORTED_FIELDS)) {
    //         return;
    //     }
    //
    //     // resolve references before processing
    //     $resolvedValue = $this->resolveValueWithReferences($value);
    //     $processedValue = $this->extractValue($resolvedValue);
    //
    //     if ($key === 'dc:title') {
    //         $processedData['storyTitle'] = $processedValue;
    //     }
    //
    //     $processedData['story'][$key] = $this->mergeValues(
    //         $processedData['story'][$key] ?? null,
    //         $processedValue
    //     );
    // }
    //
    // private function resolveValueWithReferences(array|string $value): array|string
    // {
    //     if (is_array($value)) {
    //         return array_map([$this, 'resolveValueWithReferences'], $value);
    //     }
    //
    //     if ($this->isIdReference($value)) {
    //         $resolved = $this->resolveReference($value['@id']);
    //         if ($resolved) {
    //             // return the resolved content, preferring @value if available
    //             return $this->extractMeaningfulContent($resolved);
    //         }
    //         // if reference can't be resolved, return the @id as fallback
    //         return $value['@id'];
    //     }
    //
    //     return $value;
    // }
    //
    // private function extractMeaningfulContent(array $resolvedItem): array
    // {
    //     $meaningfulFields = ['@value', 'skos:prefLabel', 'rdfs:label', 'dc:title', 'foaf:name'];
    //
    //     foreach ($meaningfulFields as $field) {
    //         if (isset($resolvedItem[$field])) {
    //             return [$field => $resolvedItem[$field]];
    //         }
    //     }
    //
    //     // if no meaningful field found, return the whole item minus @id and @type
    //     $filtered = array_filter($resolvedItem, function($key) {
    //         return !in_array($key, ['@id', '@type']);
    //     }, ARRAY_FILTER_USE_KEY);
    //
    //     return $filtered ?: ['@id' => $resolvedItem['@id'] ?? 'unknown'];
    // }
    //
    // private function extractValue(mixed $value): string
    // {
    //     if (is_array($value)) {
    //         return $this->processArrayValue($value);
    //     }
    //
    //     if (is_object($value)) {
    //         return $this->processObjectValue($value);
    //     }
    //
    //     return $this->cleanValue($value);
    // }
    //
    // private function processArrayValue(array $value): string
    // {
    //     $results = [];
    //     foreach ($value as $item) {
    //         if (is_object($item)) {
    //             $results[] = $this->processObjectValue($item);
    //         } elseif (is_array($item)) {
    //             $results[] = $this->processArrayValue($item);
    //         } else {
    //             $results[] = $this->cleanValue($item);
    //         }
    //     }
    //     return implode(' || ', array_filter($results));
    // }
    //
    // private function processObjectValue(object $value): string
    // {
    //     $valueArray = (array) $value;
    //
    //     if (isset($valueArray['@value'])) {
    //         return $this->cleanValue($valueArray['@value']);
    //     }
    //
    //     if (isset($valueArray['@id'])) {
    //         return $this->cleanValue($valueArray['@id']);
    //     }
    //
    //     // handle other meaningful fields first
    //     $meaningfulFields = ['skos:prefLabel', 'rdfs:label', 'dc:title', 'foaf:name'];
    //     foreach ($meaningfulFields as $field) {
    //         if (isset($valueArray[$field])) {
    //             return $this->extractValue($valueArray[$field]);
    //         }
    //     }
    //
    //     return '';
    // }
    //
    // private function cleanValue(mixed $value): string
    // {
    //     if (is_null($value)) {
    //         return '';
    //     }
    //
    //     return trim($value);
    // }
    //
    // private function mergeValues(?string $existing, string $new): string
    // {
    //     if ($new === '') {
    //         return $existing ?? '';
    //     }
    //
    //     if ($existing === null || $existing === '') {
    //         return $new;
    //     }
    //
    //     // don't fill with redundant values
    //     if ($existing === $new) {
    //         return $existing;
    //     }
    //
    //     return $existing . ' || ' . $new;
    // }
    //
    // private function processPlace(array $item, array &$processedData): void
    // {
    //     if ($processedData['placeAdded']) return;
    //
    //     $coordinates = $this->extractCoordinates($item);
    //     if ($coordinates) {
    //         $processedData['story']['PlaceLatitude'] = $coordinates['lat'];
    //         $processedData['story']['PlaceLongitude'] = $coordinates['lng'];
    //     }
    //
    //     if (isset($item['skos:prefLabel'])) {
    //         $resolvedLabel = $this->resolveValueWithReferences($item['skos:prefLabel']);
    //         $processedData['story']['PlaceName'] = $this->extractValue($resolvedLabel);
    //     }
    //
    //     $processedData['placeAdded'] = true;
    // }
    //
    // private function extractCoordinates(array $item): ?array
    // {
    //     $coordFields = [
    //         ['lat' => 'geo:lat', 'lng' => 'geo:long'],
    //         ['lat' => 'wgs84_pos:lat', 'lng' => 'wgs84_pos:long'],
    //         ['lat' => 'geo:latitude', 'lng' => 'geo:longitude']
    //     ];
    //
    //     foreach ($coordFields as $fields) {
    //         if (isset($item[$fields['lat']], $item[$fields['lng']])) {
    //             $lat = $this->resolveValueWithReferences($item[$fields['lat']]);
    //             $lng = $this->resolveValueWithReferences($item[$fields['lng']]);
    //
    //             return [
    //                 'lat' => $this->extractNumericValue($lat),
    //                 'lng' => $this->extractNumericValue($lng)
    //             ];
    //         }
    //     }
    //
    //     return null;
    // }
    //
    // private function extractNumericValue($value): ?float
    // {
    //     if (is_numeric($value)) {
    //         return (float) $value;
    //     }
    //
    //     if (is_array($value) && isset($value['@value']) && is_numeric($value['@value'])) {
    //         return (float) $value['@value'];
    //     }
    //
    //     return null;
    // }
    //
    // private function processAgent(array $item, array &$processedData): void
    // {
    //     if (!isset($item['skos:prefLabel'])) return;
    //
    //     $resolvedLabel = $this->resolveValueWithReferences($item['skos:prefLabel']);
    //     $agentData = $this->extractValue($resolvedLabel);
    //
    //     if (isset($item['@id'])) {
    //         $agentData .= ' | ' . $item['@id'];
    //     }
    //
    //     $processedData['story']['edm:agent'] = $this->mergeValues(
    //         $processedData['story']['edm:agent'] ?? null,
    //         $agentData
    //     );
    // }
    //
    // private function processWebResource(array $item, array &$processedData): void
    // {
    //     if (isset($item['dcterms:isReferencedBy'])) {
    //         $manifestRef = $this->resolveValueWithReferences($item['dcterms:isReferencedBy']);
    //         $manifestUrl = null;
    //
    //         if (is_array($manifestRef) && isset($manifestRef['@id'])) {
    //             $manifestUrl = $manifestRef['@id'];
    //         } elseif (is_string($manifestRef)) {
    //             $manifestUrl = $manifestRef;
    //         }
    //
    //         if ($manifestUrl && $this->isValidManifestUrl($manifestUrl)) {
    //             $processedData['manifestUrl'] = $manifestUrl;
    //         }
    //     }
    //
    //     if (isset($item['@id'])) {
    //         if (isset($item['ebucore:hasMimeType']) &&
    //             str_contains($item['ebucore:hasMimeType'], 'application/pdf')) {
    //             $processedData['pdfImage'] = $item['@id'];
    //         } else {
    //             $processedData['imageLinks'][] = $item['@id'];
    //         }
    //     }
    // }
    //
    // private function processProvidedCHO(array $item, array &$processedData): void
    // {
    //     if (!isset($item['@id'])) return;
    //
    //     $processedData['externalRecordId'] = $item['@id'];
    //     $parts = explode('/', $item['@id']);
    //     $end = end($parts);
    //     $secondEnd = prev($parts);
    //     $processedData['recordId'] = '/' . $secondEnd . '/' . $end;
    // }
    //
    // private function isValidManifestUrl(string $url): bool
    // {
    //     return filter_var($url, FILTER_VALIDATE_URL) !== false;
    // }
    //
    public function getNodeIndexKeys(): array
    {
        return array_keys($this->nodeIndex);
    }

    public function getNodeIndex(): array
    {
        return $this->nodeIndex;
    }

    public function canResolve(string $id): bool
    {
        return isset($this->nodeIndex[$id]);
    }
}
