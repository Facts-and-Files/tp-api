<?php
/**
 *  EDM JsonLd Parser
 *  Minimal, dependency-free helper that turns an EDM JSON-LD string
 *  into an easy-to-use PHP structure.
 *
 *  Author:  Your-Name-Here
 *  Licence: MIT
 */

declare(strict_types=1);

class EdmJsonLdParser
{
    private array $graph;        // Normalised graph (list of nodes)
    private array $context;      // JSON-LD @context
    private array $index;        // Fast id → node lookup

    /**
     * @throws RuntimeException on JSON or EDM structural errors
     */
    public function __construct(string $jsonLd)
    {
        $raw = json_decode($jsonLd, true, 512, JSON_THROW_ON_ERROR);

        // Flatten to a "graph" so that @graph or a single resource both become
        // a list of associative arrays.
        if (isset($raw['@graph'])) {
            $this->graph = $raw['@graph'];
        } else {
            $this->graph = [$raw];
        }
        $this->context = $raw['@context'] ?? [];
        $this->index   = [];

        // Build id → node index
        foreach ($this->graph as $node) {
            if (isset($node['@id'])) {
                $this->index[$node['@id']] = $node;
            }
        }
    }

    /* ------------------------------------------------------------------
     *  Public helpers
     * ------------------------------------------------------------------ */

    /**
     * Return every top-level node that has the given rdf:type
     * (short form like "edm:ProvidedCHO" or full IRI).
     *
     * @return array<array>  List of raw JSON-LD nodes
     */
    public function findByType(string $rdfType): array
    {
        $typeToFind = $this->expandCurie($rdfType);
        return array_filter($this->graph, function (array $node) use ($typeToFind) {
            $types = (array) ($node['@type'] ?? []);
            return in_array($typeToFind, $types, true);
        });
    }

    /**
     * Get the ore:Aggregation node that aggregates the supplied CHO id.
     * Returns null if nothing found.
     */
    public function getAggregationFor(string $choId): ?array
    {
        foreach ($this->graph as $node) {
            if (in_array('ore:Aggregation', (array) ($node['@type'] ?? []), true) &&
                ($node['edm:aggregatedCHO'] ?? null) === $choId) {
                return $node;
            }
        }
        return null;
    }

    /**
     * Dereference a node by its @id (or null if not present).
     */
    public function getNode(string $id): ?array
    {
        return $this->index[$id] ?? null;
    }

    /**
     * Convert a CURIE like "edm:ProvidedCHO" into the full IRI using
     * the JSON-LD context.  If no prefix is given we return the string as-is.
     */
    public function expandCurie(string $curie): string
    {
        if (strpos($curie, ':') === false) {
            return $curie;
        }
        [$prefix, $local] = explode(':', $curie, 2);
        $context = $this->context;
        if (is_array($context) && isset($context[$prefix])) {
            return $context[$prefix] . $local;
        }
        return $curie; // fallback
    }

    /**
     * Pretty-print the entire graph (for debugging).
     */
    public function dump(): void
    {
        echo json_encode($this->graph, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

$jsonLd = file_get_contents('edm-case-2.json');
$parser  = new EdmJsonLdParser($jsonLd);

$chos  = $parser->findByType('edm:agent');
$first = $chos[0]['dc:title']['en'] ?? 'untitled';

// $parser->dump();
print_r($chos);
