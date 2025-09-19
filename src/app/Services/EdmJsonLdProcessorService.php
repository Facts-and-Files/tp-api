<?php

/**
 * Class EdmJsonLdProcessorService
 *
 * Processes EDM JSON-LD data into structured PHP arrays and provides utility methods
 * for working with literals, entity references, and manifest URLs.
 *
 * This service:
 *  - Builds a node index of all entities in the JSON-LD graph for fast lookup.
 *  - Resolves values of properties to Literal objects, including references and inline entities.
 *  - Extracts key fields such as manifest URL, story title, and image links according to configured mappings.
 *
 * Public Methods:
 *
 *  processJsonLd(array $jsonLdData): array
 *      Processes the JSON-LD input and returns a normalized array with extracted fields.
 *
 *  getLiterals(string $entityId, string $property): array
 *      Returns an array of Literal objects for a given entity ID and property.
 *
 *  resolveProperty(string $property): array
 *      Aggregates and returns all Literal objects for a given property across all entities in the graph.
 *
 *  resolveEntity(string $id): ?array
 *      Returns the raw JSON-LD array for a given entity ID if it exists in the node index, or null.
 *
 *  getNodeIndex(): array
 *      Returns the full node index built from the JSON-LD data.
 *
 *  getNodeIndexKeys(): array
 *      Returns all entity IDs indexed from the JSON-LD data.
 *
 *  canResolve(string $id): bool
 *      Returns true if the given entity ID exists in the node index, false otherwise.
 *
 * Notes:
 *  - All literal values returned are instances of App\EDM\Literal.
 *  - The service relies on configuration mappings (edm.mappings) to locate manifest URLs,
 *    story titles, images, and other key fields.
 *  - Designed to handle JSON-LD with nested @graph structures, references, and language-tagged literals.
 */

namespace App\Services;

use App\EDM\Literal;
use App\EDM\LiteralFactory;
use App\EDM\LiteralHelper;
use App\Exceptions\InvalidManifestUrlException;
use Illuminate\Support\Collection;

class EdmJsonLdProcessorService
{
    protected array $nodeIndex = [];
    protected array $mappings = [];

    public function processJsonLd(array $jsonLdData): array
    {
        $this->mappings = config('edm.mappings');
        $this->buildNodeIndex($jsonLdData);

        $processedData = [
            'story'            => [],
            'recordId'         => '',
            'externalRecordId' => '',
            'storyTitle'       => '',
            'manifestUrl'      => $this->extractManifestUri('manifestUrl', $jsonLdData),
            'imageLink'        => '',
            'pdfImage'         => '',
            'imageLinks'       => '',
            'placeAdded'       => false,
        ];

        return $processedData;
    }

    // -------------------------
    // Node index handling
    // -------------------------

    protected function buildNodeIndex(array $nodes): void
    {
        if (isset($nodes['@id']) && isset($nodes['@type'])) {
            $this->nodeIndex[$nodes['@id']] = $nodes;
        }

        foreach ($nodes as $node) {
            if (is_array($node)) {
                if (isset($node['@id']) && isset($node['@type'])) {
                    $this->nodeIndex[$node['@id']] = $node;
                }
                $this->buildNodeIndex($node);
            }
        }
    }

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

    public function resolveEntity(string $id): ?array
    {
        return $this->nodeIndex[$id] ?? null;
    }

    // -------------------------
    // Literal resolution
    // -------------------------

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

    protected function resolveValue(mixed $value): array
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

    protected function extractLabelFromEntity(array $entity): array
    {
        $candidates = ['skos:prefLabel', 'foaf:name', 'dc:title'];

        foreach ($candidates as $prop) {
            if (isset($entity[$prop])) {
                return LiteralFactory::fromJsonLd($entity[$prop]);
            }
        }

        return [];
    }

    // -------------------------
    // Field extraction
    // -------------------------

    public function extractManifestUri(string $name, array $jsonLdData): string
    {
        $manifestUrl = $this->extractFieldValue('manifestUrl', $jsonLdData);

        if (!$this->isValidManifestUrl($manifestUrl)) {
            throw new InvalidManifestUrlException("Manifest URL is missing or invalid: {$manifestUrl}");
        }

        return $manifestUrl;
    }

    private function isValidManifestUrl(?string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    protected function extractFieldValue(string $field, array $jsonLdData): ?string
    {
        $config = $this->mappings[$field] ?? null;
        if (!$config) {
            return null;
        }

        $dataCollection  = collect($jsonLdData);
        $graphCollection = collect(data_get($dataCollection, '@graph', $dataCollection));

        foreach ($config['paths'] as $path) {
            // try main collection
            if ($raw = $this->extractByPathAndType($dataCollection, $path['path'])) {
                return $this->extractResolvedValue($raw);
            }

            // try graph collection with type
            if (!empty($path['type'])) {
                if ($raw = $this->extractByPathAndType($graphCollection, $path['path'], $path['type'])) {
                    return $this->extractResolvedValue($raw);
                }
            }
        }

        return null;
    }

    protected function extractFieldValues(string $field, array $jsonLdData): array
    {
        $config          = $this->mappings[$field] ?? null;
        if (!$config) {
            return [];
        }

        $dataCollection  = collect($jsonLdData);
        $graphCollection = collect(data_get($dataCollection, '@graph', $dataCollection));
        $results         = [];

        foreach ($config['paths'] as $path) {
            $rawValues = $this->extractAllByPathAndType($dataCollection, $graphCollection, $path);

            foreach ($rawValues as $raw) {
                $normalized = $this->extractResolvedValue($raw);
                if ($normalized !== null) {
                    $results[] = $normalized;
                }
            }
        }

        return array_values(array_unique($results));
    }

    protected function extractResolvedValue(mixed $raw): ?string
    {
        $literals = LiteralFactory::fromJsonLd($raw);
        return LiteralHelper::toNormalizedString($literals) ?: null;
    }

    protected function extractByPathAndType(
        Collection $collection,
        string $path,
        ?string $type = null
    ): mixed {
        if (!$type) {
            return data_get($collection, $path);
        }

        return $collection
            ->filter(fn($item) => isset($item['@type']) && $item['@type'] === $type)
            ->map(fn($item) => data_get($item, $path))
            ->first();
    }

    protected function extractAllByPathAndType(
        Collection $dataCollection,
        Collection $graphCollection,
        array $pathConfig
    ): array {
        $results = [];

        if ($raw = $this->extractByPathAndType($dataCollection, $pathConfig['path'])) {
            $results[] = $raw;
        }

        if (!empty($pathConfig['type'])) {
            $graphValues = $graphCollection
                ->filter(fn($item) => isset($item['@type']) && $item['@type'] === $pathConfig['type'])
                ->map(fn($item) => data_get($item, $pathConfig['path']))
                ->all();

            $results = array_merge($results, $graphValues);
        }

        return $results;
    }
}
