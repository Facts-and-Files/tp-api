<?php

namespace App\Services\Import\EDM;

use App\Services\Import\DTO\ParsedJsonLdData;
use App\Services\Import\Support\JsonLdValueExtractor;
use App\Services\Import\Support\ParseContext;

final class EdmNodeExtractor
{
    private const STORY_FIELDS = [
        'dc:title', 'dc:description', 'dc:creator', 'dc:source',
        'dc:contributor', 'dc:publisher', 'dc:coverage', 'dc:date',
        'dc:type', 'dc:relation', 'dc:rights', 'dc:identifier', 'dc:language',
        'edm:landingPage', 'edm:country', 'edm:dataProvider', 'edm:provider',
        'edm:rights', 'edm:begin', 'edm:end', 'edm:year',
        'edm:datasetName', 'edm:isShownAt', 'edm:language', 'edm:agent',
        'dcterms:medium', 'dcterms:provenance', 'dcterms:created',
    ];

    public function __construct(
        private readonly JsonLdValueExtractor $values,
    ) {
    }

    public function extract(array $graph, ?string $iiifUrl): ParsedJsonLdData
    {
        $context = new ParseContext(
            manifestUrl: $iiifUrl ?? '',
            manifestAuthMode: $iiifUrl ? 'token' : 'public',
        );

        foreach ($graph as $node) {
            $this->extractStoryFields($node, $context);
            $this->extractInlineManifest($node, $context);
            $this->handlePlace($node, $context);
            $this->handleAgent($node, $context);
            $this->handleWebResource($node, $context);
            $this->handleProvidedCho($node, $context);
        }

        return $context->toDto();
    }

    private function extractStoryFields(array $node, ParseContext $context): void
    {
        foreach (self::STORY_FIELDS as $field) {
            if (!isset($node[$field])) {
                continue;
            }

            $value = $this->values->extractFlattened($node[$field]);
            if ($value === null || $value === '') {
                continue;
            }

            if ($field === 'dc:description') {
                $value = $this->values->sanitizeDescription($value);
            }

            $context->appendField($field, $value);
        }
    }

    private function extractInlineManifest(array $node, ParseContext $context): void
    {
        if (!isset($node['iiif_url'])) {
            return;
        }

        $manifestUrl = $this->values->extractFirstUrl($node['iiif_url']);
        if ($manifestUrl !== null) {
            $context->setManifestUrl($manifestUrl, 'token');
        }
    }

    private function handlePlace(array $node, ParseContext $context): void
    {
        if (($node['@type'] ?? null) !== 'edm:Place' || isset($context->fields['PlaceLatitude'])) {
            return;
        }

        $lat = $this->values->extractScalar($node['geo:lat'] ?? $node['wgs84_pos:lat'] ?? null);
        $lon = $this->values->extractScalar($node['geo:long'] ?? $node['wgs84_pos:long'] ?? null);

        if ($lat !== null && $lon !== null) {
            $context->fields['PlaceLatitude'] = $lat;
            $context->fields['PlaceLongitude'] = $lon;
        }

        $label = $this->values->extractScalar($node['skos:prefLabel'] ?? null);
        if ($label !== null && $label !== '') {
            $context->fields['PlaceName'] = $label;
        }
    }

    private function handleAgent(array $node, ParseContext $context): void
    {
        if (($node['@type'] ?? null) !== 'edm:Agent' || !isset($node['skos:prefLabel'])) {
            return;
        }

        $label = $this->values->extractFlattened($node['skos:prefLabel']) ?? '';
        $id = $node['@id'] ?? '';
        $agent = $label !== '' ? trim($label) : $id;

        if ($agent !== '') {
            $context->appendField('edm:agent', $agent);
        }
    }

    private function handleWebResource(array $node, ParseContext $context): void
    {
        if (!$this->isWebResource($node)) {
            return;
        }

        $manifestUrl = $this->extractManifestUrl($node);
        if ($manifestUrl !== null) {
            $context->setManifestUrl($manifestUrl, 'public');
        }

        $mimeType = $this->values->extractScalar($node['ebucore:hasMimeType'] ?? null) ?? '';
        if (str_contains($mimeType, 'application/pdf')) {
            $context->pdfImage = $node['@id'] ?? '';
        }
    }

    private function handleProvidedCho(array $node, ParseContext $context): void
    {
        if (($node['@type'] ?? null) !== 'edm:ProvidedCHO' || !isset($node['@id'])) {
            return;
        }

        $context->externalRecordId = $node['@id'];
        $parts = explode('/', $context->externalRecordId);

        if (count($parts) >= 2) {
            $context->recordId = '/' . $parts[count($parts) - 2] . '/' . end($parts);
        }
    }

    private function extractManifestUrl(array $node): ?string
    {
        foreach (['iiif_url', 'dcterms:isReferencedBy', 'rdf:value', '@id'] as $field) {
            if (!isset($node[$field])) {
                continue;
            }

            $url = $this->values->extractFirstUrl($node[$field]);
            if ($url !== null) {
                return $url;
            }
        }

        return null;
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
}
