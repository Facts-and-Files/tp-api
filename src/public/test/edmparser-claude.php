<?php

/**
 * Europeana Data Model (EDM) JSON-LD Parser
 *
 * This parser extracts and structures data from JSON-LD strings that follow
 * the Europeana Data Model specification.
 */
class EdmJsonLdParser
{
    private $data;
    private $context;
    private $entityIndex = [];
    private $resolveReferences = true;

    // Common EDM namespaces and prefixes
    private $edmNamespaces = [
        'edm' => 'http://www.europeana.eu/schemas/edm/',
        'ore' => 'http://www.openarchives.org/ore/terms/',
        'dc' => 'http://purl.org/dc/elements/1.1/',
        'dcterms' => 'http://purl.org/dc/terms/',
        'foaf' => 'http://xmlns.com/foaf/0.1/',
        'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
        'skos' => 'http://www.w3.org/2004/02/skos/core#',
        'wgs84_pos' => 'http://www.w3.org/2003/01/geo/wgs84_pos#'
    ];

    /**
     * Parse a JSON-LD string containing EDM data
     *
     * @param string $jsonLdString The JSON-LD string to parse
     * @param bool $resolveReferences Whether to resolve @id references to actual objects
     * @return array Parsed EDM data structure
     * @throws InvalidArgumentException If the JSON is invalid
     */
    public function parse($jsonLdString, $resolveReferences = true)
    {
        $this->data = json_decode($jsonLdString, true);
        $this->resolveReferences = $resolveReferences;
        $this->entityIndex = [];

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        $this->extractContext();

        // First pass: build entity index
        if ($this->resolveReferences) {
            $this->buildEntityIndex();
        }

        $result = [
            'context' => $this->context,
            'culturalHeritageObjects' => $this->extractCulturalHeritageObjects(),
            'aggregations' => $this->extractAggregations(),
            'webResources' => $this->extractWebResources(),
            'agents' => $this->extractAgents(),
            'places' => $this->extractPlaces(),
            'concepts' => $this->extractConcepts(),
            'timespans' => $this->extractTimespans()
        ];

        // Second pass: resolve references if enabled
        if ($this->resolveReferences) {
            $result = $this->resolveAllReferences($result);
        }

        return $result;
    }

    /**
     * Build an index of all entities by their @id for reference resolution
     */
    private function buildEntityIndex()
    {
        $items = $this->ensureArray($this->data);

        foreach ($items as $item) {
            if (isset($item['@id'])) {
                $this->entityIndex[$item['@id']] = $item;
            }
        }
    }

    /**
     * Resolve @id references to actual entity objects
     *
     * @param string $id The @id to resolve
     * @return array|string Returns the resolved entity or the original ID if not found
     */
    public function resolveReference($id)
    {
        if (!$this->resolveReferences) {
            return $id;
        }

        if (isset($this->entityIndex[$id])) {
            $entity = $this->entityIndex[$id];

            // Create a simplified representation to avoid circular references
            $resolved = [
                'id' => $this->getValue($entity, '@id'),
                'type' => $this->getValue($entity, '@type')
            ];

            // Add common identifying properties
            if (isset($entity['skos:prefLabel'])) {
                $resolved['prefLabel'] = $this->getMultilingualValue($entity, 'skos:prefLabel');
            }
            if (isset($entity['dc:title'])) {
                $resolved['title'] = $this->getMultilingualValue($entity, 'dc:title');
            }
            if (isset($entity['rdfs:label'])) {
                $resolved['label'] = $this->getMultilingualValue($entity, 'rdfs:label');
            }
            if (isset($entity['foaf:name'])) {
                $resolved['name'] = $this->getMultilingualValue($entity, 'foaf:name');
            }

            return $resolved;
        }

        // Return original ID if not found in index
        return $id;
    }

    /**
     * Resolve all references in the parsed data structure
     */
    private function resolveAllReferences($data)
    {
        return array_map(function($section) {
            if (is_array($section)) {
                return array_map([$this, 'resolveEntityReferences'], $section);
            }
            return $section;
        }, $data);
    }

    /**
     * Resolve references within a single entity
     */
    private function resolveEntityReferences($entity)
    {
        if (!is_array($entity)) {
            return $entity;
        }

        foreach ($entity as $key => $value) {
            if (is_string($value) && $this->isReference($value)) {
                $entity[$key] = $this->resolveReference($value);
            } elseif (is_array($value)) {
                $entity[$key] = $this->resolveArrayReferences($value);
            }
        }

        return $entity;
    }

    /**
     * Resolve references within an array
     */
    private function resolveArrayReferences($array)
    {
        if ($this->isAssociativeArray($array)) {
            // Language map or object - resolve recursively
            foreach ($array as $key => $value) {
                if (is_string($value) && $this->isReference($value)) {
                    $array[$key] = $this->resolveReference($value);
                } elseif (is_array($value)) {
                    $array[$key] = $this->resolveArrayReferences($value);
                }
            }
        } else {
            // Simple array - resolve each element
            foreach ($array as $index => $value) {
                if (is_string($value) && $this->isReference($value)) {
                    $array[$index] = $this->resolveReference($value);
                } elseif (is_array($value)) {
                    $array[$index] = $this->resolveArrayReferences($value);
                }
            }
        }

        return $array;
    }

    /**
     * Check if a string looks like a reference (URI or blank node)
     */
    private function isReference($value)
    {
        if (!is_string($value)) {
            return false;
        }

        // Check for HTTP(S) URIs
        if (preg_match('/^https?:\/\//', $value)) {
            return true;
        }

        // Check for URN or other URI schemes
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $value)) {
            return true;
        }

        // Check for blank nodes
        if (preg_match('/^_:/', $value)) {
            return true;
        }

        // Check for relative references that start with #
        if (preg_match('/^#/', $value)) {
            return true;
        }

        return false;
    }

    /**
     * Get all entities that reference a specific @id
     *
     * @param string $targetId The @id to find references to
     * @return array Array of entities that reference the target ID
     */
    public function findReferencesTo($targetId)
    {
        $references = [];
        $items = $this->ensureArray($this->data);

        foreach ($items as $item) {
            if ($this->entityReferences($item, $targetId)) {
                $references[] = $item;
            }
        }

        return $references;
    }

    /**
     * Check if an entity references a specific @id
     */
    private function entityReferences($entity, $targetId)
    {
        foreach ($entity as $key => $value) {
            if ($key === '@id') {
                continue; // Skip the entity's own ID
            }

            if (is_string($value) && $value === $targetId) {
                return true;
            } elseif (is_array($value)) {
                if ($this->arrayContainsReference($value, $targetId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if an array contains a reference to a specific @id
     */
    private function arrayContainsReference($array, $targetId)
    {
        foreach ($array as $value) {
            if (is_string($value) && $value === $targetId) {
                return true;
            } elseif (is_array($value)) {
                if (isset($value['@id']) && $value['@id'] === $targetId) {
                    return true;
                }
                if ($this->arrayContainsReference($value, $targetId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Extract the JSON-LD context
     */
    private function extractContext()
    {
        $this->context = isset($this->data['@context']) ? $this->data['@context'] : [];
    }

    /**
     * Extract Cultural Heritage Objects (edm:ProvidedCHO)
     */
    private function extractCulturalHeritageObjects()
    {
        $chos = [];
        $items = $this->ensureArray($this->data);

        foreach ($items as $item) {
            if ($this->hasType($item, 'edm:ProvidedCHO')) {
                $cho = [
                    'id' => $this->getValue($item, '@id'),
                    'type' => $this->getValue($item, '@type'),
                    'title' => $this->getMultilingualValue($item, 'dc:title'),
                    'description' => $this->getMultilingualValue($item, 'dc:description'),
                    'creator' => $this->getMultilingualValue($item, 'dc:creator'),
                    'contributor' => $this->getMultilingualValue($item, 'dc:contributor'),
                    'publisher' => $this->getMultilingualValue($item, 'dc:publisher'),
                    'date' => $this->getMultilingualValue($item, 'dc:date'),
                    'created' => $this->getValue($item, 'dcterms:created'),
                    'issued' => $this->getValue($item, 'dcterms:issued'),
                    'type_concept' => $this->getValue($item, 'dc:type'),
                    'format' => $this->getMultilingualValue($item, 'dc:format'),
                    'medium' => $this->getValue($item, 'dcterms:medium'),
                    'extent' => $this->getMultilingualValue($item, 'dcterms:extent'),
                    'language' => $this->getValue($item, 'dc:language'),
                    'subject' => $this->getMultilingualValue($item, 'dc:subject'),
                    'coverage' => $this->getMultilingualValue($item, 'dc:coverage'),
                    'spatial' => $this->getValue($item, 'dcterms:spatial'),
                    'temporal' => $this->getValue($item, 'dcterms:temporal'),
                    'rights' => $this->getValue($item, 'dc:rights'),
                    'identifier' => $this->getMultilingualValue($item, 'dc:identifier'),
                    'source' => $this->getMultilingualValue($item, 'dc:source'),
                    'relation' => $this->getMultilingualValue($item, 'dc:relation'),
                    'currentLocation' => $this->getValue($item, 'edm:currentLocation'),
                    'isRelatedTo' => $this->getValue($item, 'edm:isRelatedTo'),
                    'hasPart' => $this->getValue($item, 'dcterms:hasPart'),
                    'isPartOf' => $this->getValue($item, 'dcterms:isPartOf')
                ];

                $chos[] = array_filter($cho, function($value) {
                    return $value !== null && $value !== [];
                });
            }
        }

        return $chos;
    }

    /**
     * Extract Aggregations (ore:Aggregation)
     */
    private function extractAggregations()
    {
        $aggregations = [];
        $items = $this->ensureArray($this->data);

        foreach ($items as $item) {
            if ($this->hasType($item, 'ore:Aggregation')) {
                $aggregation = [
                    'id' => $this->getValue($item, '@id'),
                    'type' => $this->getValue($item, '@type'),
                    'aggregatedCHO' => $this->getValue($item, 'edm:aggregatedCHO'),
                    'dataProvider' => $this->getValue($item, 'edm:dataProvider'),
                    'provider' => $this->getValue($item, 'edm:provider'),
                    'isShownAt' => $this->getValue($item, 'edm:isShownAt'),
                    'isShownBy' => $this->getValue($item, 'edm:isShownBy'),
                    'hasView' => $this->getValue($item, 'edm:hasView'),
                    'object' => $this->getValue($item, 'edm:object'),
                    'rights' => $this->getValue($item, 'edm:rights'),
                    'ugc' => $this->getValue($item, 'edm:ugc'),
                    'intermediateProvider' => $this->getValue($item, 'edm:intermediateProvider')
                ];

                $aggregations[] = array_filter($aggregation, function($value) {
                    return $value !== null;
                });
            }
        }

        return $aggregations;
    }

    /**
     * Extract Web Resources (edm:WebResource)
     */
    private function extractWebResources()
    {
        $webResources = [];
        $items = $this->ensureArray($this->data);

        foreach ($items as $item) {
            if ($this->hasType($item, 'edm:WebResource')) {
                $webResource = [
                    'id' => $this->getValue($item, '@id'),
                    'type' => $this->getValue($item, '@type'),
                    'rights' => $this->getValue($item, 'dc:rights'),
                    'format' => $this->getValue($item, 'dc:format'),
                    'extent' => $this->getValue($item, 'dcterms:extent'),
                    'isFormatOf' => $this->getValue($item, 'dcterms:isFormatOf'),
                    'created' => $this->getValue($item, 'dcterms:created'),
                    'conformsTo' => $this->getValue($item, 'dcterms:conformsTo'),
                    'hasColorSpace' => $this->getValue($item, 'edm:hasColorSpace'),
                    'componentColor' => $this->getValue($item, 'edm:componentColor')
                ];

                $webResources[] = array_filter($webResource, function($value) {
                    return $value !== null;
                });
            }
        }

        return $webResources;
    }

    /**
     * Extract Agents (edm:Agent)
     */
    private function extractAgents()
    {
        $agents = [];
        $items = $this->ensureArray($this->data);

        foreach ($items as $item) {
            if ($this->hasType($item, 'edm:Agent')) {
                $agent = [
                    'id' => $this->getValue($item, '@id'),
                    'type' => $this->getValue($item, '@type'),
                    'prefLabel' => $this->getMultilingualValue($item, 'skos:prefLabel'),
                    'altLabel' => $this->getMultilingualValue($item, 'skos:altLabel'),
                    'note' => $this->getMultilingualValue($item, 'skos:note'),
                    'begin' => $this->getValue($item, 'edm:begin'),
                    'end' => $this->getValue($item, 'edm:end'),
                    'dateOfBirth' => $this->getValue($item, 'rdfs:seeAlso'),
                    'dateOfDeath' => $this->getValue($item, 'edm:wasPresentAt'),
                    'placeOfBirth' => $this->getValue($item, 'edm:placeOfBirth'),
                    'placeOfDeath' => $this->getValue($item, 'edm:placeOfDeath'),
                    'profession' => $this->getMultilingualValue($item, 'edm:hasProfession'),
                    'sameAs' => $this->getValue($item, 'owl:sameAs')
                ];

                $agents[] = array_filter($agent, function($value) {
                    return $value !== null && $value !== [];
                });
            }
        }

        return $agents;
    }

    /**
     * Extract Places (edm:Place)
     */
    private function extractPlaces()
    {
        $places = [];
        $items = $this->ensureArray($this->data);

        foreach ($items as $item) {
            if ($this->hasType($item, 'edm:Place')) {
                $place = [
                    'id' => $this->getValue($item, '@id'),
                    'type' => $this->getValue($item, '@type'),
                    'prefLabel' => $this->getMultilingualValue($item, 'skos:prefLabel'),
                    'altLabel' => $this->getMultilingualValue($item, 'skos:altLabel'),
                    'note' => $this->getMultilingualValue($item, 'skos:note'),
                    'latitude' => $this->getValue($item, 'wgs84_pos:lat'),
                    'longitude' => $this->getValue($item, 'wgs84_pos:long'),
                    'altitude' => $this->getValue($item, 'wgs84_pos:alt'),
                    'isPartOf' => $this->getValue($item, 'dcterms:isPartOf'),
                    'hasPart' => $this->getValue($item, 'dcterms:hasPart'),
                    'sameAs' => $this->getValue($item, 'owl:sameAs')
                ];

                $places[] = array_filter($place, function($value) {
                    return $value !== null && $value !== [];
                });
            }
        }

        return $places;
    }

    /**
     * Extract Concepts (edm:Concept)
     */
    private function extractConcepts()
    {
        $concepts = [];
        $items = $this->ensureArray($this->data);

        foreach ($items as $item) {
            if ($this->hasType($item, 'skos:Concept')) {
                $concept = [
                    'id' => $this->getValue($item, '@id'),
                    'type' => $this->getValue($item, '@type'),
                    'prefLabel' => $this->getMultilingualValue($item, 'skos:prefLabel'),
                    'altLabel' => $this->getMultilingualValue($item, 'skos:altLabel'),
                    'hiddenLabel' => $this->getMultilingualValue($item, 'skos:hiddenLabel'),
                    'note' => $this->getMultilingualValue($item, 'skos:note'),
                    'broader' => $this->getValue($item, 'skos:broader'),
                    'narrower' => $this->getValue($item, 'skos:narrower'),
                    'related' => $this->getValue($item, 'skos:related'),
                    'inScheme' => $this->getValue($item, 'skos:inScheme'),
                    'exactMatch' => $this->getValue($item, 'skos:exactMatch'),
                    'closeMatch' => $this->getValue($item, 'skos:closeMatch')
                ];

                $concepts[] = array_filter($concept, function($value) {
                    return $value !== null && $value !== [];
                });
            }
        }

        return $concepts;
    }

    /**
     * Extract Time Spans (edm:TimeSpan)
     */
    private function extractTimespans()
    {
        $timespans = [];
        $items = $this->ensureArray($this->data);

        foreach ($items as $item) {
            if ($this->hasType($item, 'edm:TimeSpan')) {
                $timespan = [
                    'id' => $this->getValue($item, '@id'),
                    'type' => $this->getValue($item, '@type'),
                    'prefLabel' => $this->getMultilingualValue($item, 'skos:prefLabel'),
                    'altLabel' => $this->getMultilingualValue($item, 'skos:altLabel'),
                    'note' => $this->getMultilingualValue($item, 'skos:note'),
                    'begin' => $this->getValue($item, 'edm:begin'),
                    'end' => $this->getValue($item, 'edm:end'),
                    'isPartOf' => $this->getValue($item, 'dcterms:isPartOf'),
                    'hasPart' => $this->getValue($item, 'dcterms:hasPart')
                ];

                $timespans[] = array_filter($timespan, function($value) {
                    return $value !== null && $value !== [];
                });
            }
        }

        return $timespans;
    }

    /**
     * Helper method to check if an item has a specific type
     */
    private function hasType($item, $type)
    {
        if (!isset($item['@type'])) {
            return false;
        }

        $types = $this->ensureArray($item['@type']);
        return in_array($type, $types);
    }

    /**
     * Get a simple value from an item
     */
    private function getValue($item, $key)
    {
        if (!isset($item[$key])) {
            return null;
        }

        $value = $item[$key];

        // Handle array of values
        if (is_array($value)) {
            if (count($value) === 1) {
                $value = reset($value);
            }

            // Handle objects with @id
            if (is_array($value) && isset($value['@id'])) {
                return $value['@id'];
            }

            // Handle array of objects with @id
            if (is_array($value)) {
                $ids = [];
                foreach ($value as $v) {
                    if (is_array($v) && isset($v['@id'])) {
                        $ids[] = $v['@id'];
                    } elseif (is_string($v)) {
                        $ids[] = $v;
                    }
                }
                return count($ids) > 1 ? $ids : (count($ids) === 1 ? $ids[0] : null);
            }
        }

        // Handle object with @id
        if (is_array($value) && isset($value['@id'])) {
            return $value['@id'];
        }

        return $value;
    }

    /**
     * Get multilingual value (handles language maps)
     */
    private function getMultilingualValue($item, $key)
    {
        if (!isset($item[$key])) {
            return null;
        }

        $value = $item[$key];

        // Handle simple string
        if (is_string($value)) {
            return $value;
        }

        // Handle array of strings
        if (is_array($value) && !$this->isAssociativeArray($value)) {
            return count($value) === 1 ? $value[0] : $value;
        }

        // Handle language map (associative array with language codes)
        if (is_array($value) && $this->isAssociativeArray($value)) {
            // Check if it's a language map
            $hasLanguageCodes = true;
            foreach (array_keys($value) as $k) {
                if (!preg_match('/^[a-z]{2,3}(-[A-Z]{2})?$/', $k) && $k !== 'def') {
                    $hasLanguageCodes = false;
                    break;
                }
            }

            if ($hasLanguageCodes) {
                return $value; // Return the language map as-is
            }
        }

        // Handle array of objects with @value and @language
        if (is_array($value)) {
            $result = [];
            foreach ($value as $v) {
                if (is_array($v) && isset($v['@value'])) {
                    $lang = isset($v['@language']) ? $v['@language'] : 'def';
                    $result[$lang] = $v['@value'];
                } elseif (is_string($v)) {
                    $result[] = $v;
                }
            }

            if (!empty($result)) {
                return count($result) === 1 ? reset($result) : $result;
            }
        }

        return $value;
    }

    /**
     * Ensure value is an array
     */
    private function ensureArray($value)
    {
        if (isset($value['@graph'])) {
            return $value['@graph'];
        }

        return is_array($value) && !$this->isAssociativeArray($value) ? $value : [$value];
    }

    /**
     * Check if array is associative
     */
    private function isAssociativeArray($array)
    {
        if (!is_array($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Get summary statistics of parsed data
     */
    public function getStatistics($parsedData)
    {
        return [
            'culturalHeritageObjects' => count($parsedData['culturalHeritageObjects']),
            'aggregations' => count($parsedData['aggregations']),
            'webResources' => count($parsedData['webResources']),
            'agents' => count($parsedData['agents']),
            'places' => count($parsedData['places']),
            'concepts' => count($parsedData['concepts']),
            'timespans' => count($parsedData['timespans']),
            'totalReferences' => $this->resolveReferences ? count($this->entityIndex) : 0
        ];
    }

    /**
     * Get entity by ID from the internal index
     *
     * @param string $id The @id to look up
     * @return array|null The entity or null if not found
     */
    public function getEntityById($id)
    {
        return isset($this->entityIndex[$id]) ? $this->entityIndex[$id] : null;
    }

    /**
     * Get all entity IDs in the dataset
     *
     * @return array Array of all @id values
     */
    public function getAllEntityIds()
    {
        return array_keys($this->entityIndex);
    }

    public function dump($data)
    {
        $data = [
            'Parsed EDM data' => $data,
            'Statistics' => $this->getStatistics($data),
            'Entities' => $this->getAllEntityIds(),
        ];

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

// Example usage:
try {
    $parser = new EdmJsonLdParser();

    $jsonLdString = file_get_contents('edm-case-2.json');
    // Parse with reference resolution (default)
    $result = $parser->parse($jsonLdString, true);

    $parser->dump($result);

    // Parse without reference resolution (faster for large datasets)
    // $result = $parser->parse($jsonLdString, false);

    // echo "Parsed EDM data:\n";
    // print_r($result);

    // echo "\nStatistics:\n";
    // print_r($parser->getStatistics($result));

    // Look up a specific entity
    // $entity = $parser->getEntityById('http://example.org/agent/123');
    // if ($entity) {
    //     echo "\nFound entity:\n";
    //     print_r($entity);
    // }

    // Find all references to an entity
    // $references = $parser->findReferencesTo('http://example.org/agent/123');
    // echo "\nEntities referencing this agent: " . count($references) . "\n";

    // Get all entity IDs
    // $allIds = $parser->getAllEntityIds();
    // echo "\nTotal entities with @id: " . count($allIds) . "\n";

} catch (Exception $e) {
    echo "Error parsing JSON-LD: " . $e->getMessage();
}
