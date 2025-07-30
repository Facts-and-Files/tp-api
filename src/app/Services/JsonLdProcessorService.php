<?php

namespace App\Services;

class JsonLdProcessorService
{
    private const SUPPORTED_FIELDS = [
        'dc:title', 'dc:description', 'edm:landingPage', 'dc:creator',
        'dc:source', 'edm:country', 'edm:dataProvider', 'edm:provider',
        'edm:rights', 'edm:begin', 'edm:end', 'dc:contributor', 'edm:year',
        'dc:publisher', 'dc:coverage', 'dc:date', 'dc:type', 'dc:relation',
        'dcterms:medium', 'edm:datasetName', 'edm:isShownAt', 'dc:rights',
        'dc:identifier', 'dc:language', 'edm:language', 'edm:agent',
        'dcterms:provenance', 'dcterms:created'
    ];

    public function processJsonLd(array $dataArray): array
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

        foreach ($dataArray as $item) {
            $this->processJsonLdItem($item, $processedData);
        }

        return $processedData;
    }

    private function processJsonLdItem(array $item, array &$processedData): void
    {
        foreach ($item as $key => $value) {
            match ($key) {
                '@type' => $this->processTypeField($item, $value, $processedData),
                'iiif_url' => $processedData['manifestUrl'] = $value,
                default => $this->processRegularField($key, $value, $processedData)
            };
        }
    }

    private function processTypeField(array $item, $value, array &$processedData): void
    {
        $type = is_array($value) ? $value[0] : $value;

        switch ($type) {
            case 'edm:Place':
                $this->processPlace($item, $processedData);
                break;
            case 'edm:Agent':
                $this->processAgent($item, $processedData);
                break;
            case 'edm:WebResource':
                $this->processWebResource($item, $processedData);
                break;
            case 'edm:ProvidedCHO':
                $this->processProvidedCHO($item, $processedData);
                break;
        }
    }

    private function processRegularField(string $key, $value, array &$processedData): void
    {
        if (!in_array($key, self::SUPPORTED_FIELDS)) {
            return;
        }

        $processedValue = $this->extractValue($value);

        if ($key === 'dc:title') {
            $processedData['storyTitle'] = $processedValue;
        }

        $processedData['story'][$key] = $this->mergeValues(
            $processedData['story'][$key] ?? null,
            $processedValue
        );
    }

    private function extractValue($value): string
    {
        if (is_array($value)) {
            return $this->processArrayValue($value);
        }

        if (is_object($value)) {
            return $this->processObjectValue($value);
        }

        return $this->cleanValue($value);
    }

    private function processArrayValue(array $value): string
    {
        $results = [];
        foreach ($value as $item) {
            if (is_object($item)) {
                $results[] = $this->processObjectValue($item);
            } else {
                $results[] = $this->cleanValue($item);
            }
        }
        return implode(' || ', $results);
    }

    private function processObjectValue(object $value): string
    {
        $valueArray = (array) $value;

        if (isset($valueArray['@value'])) {
            return $this->cleanValue($valueArray['@value']);
        }

        if (isset($valueArray['@id'])) {
            return $this->cleanValue($valueArray['@id']);
        }

        return '';
    }

    private function cleanValue($value): string
    {
        return preg_replace('/["{}\[\]]/', '', str_replace(['\\"', ','], ['', ' | '], $value));
    }

    private function mergeValues(?string $existing, string $new): string
    {
        return $existing ? $existing . ' || ' . $new : $new;
    }

    private function processPlace(array $item, array &$processedData): void
    {
        if ($processedData['placeAdded']) return;

        // Process coordinates
        if (isset($item['geo:lat'], $item['geo:long'])) {
            $processedData['story']['PlaceLatitude'] = $item['geo:lat'];
            $processedData['story']['PlaceLongitude'] = $item['geo:long'];
        } elseif (isset($item['wgs84_pos:lat'], $item['wgs84_pos:long'])) {
            $processedData['story']['PlaceLatitude'] = $item['wgs84_pos:lat'];
            $processedData['story']['PlaceLongitude'] = $item['wgs84_pos:long'];
        }

        // Process place name
        if (isset($item['skos:prefLabel'])) {
            $processedData['story']['PlaceName'] = $this->extractValue($item['skos:prefLabel']);
        }

        $processedData['placeAdded'] = true;
    }

    private function processAgent(array $item, array &$processedData): void
    {
        if (!isset($item['skos:prefLabel'])) return;

        $agentData = $this->extractValue($item['skos:prefLabel']);
        if (isset($item['@id'])) {
            $agentData .= ' | ' . $item['@id'];
        }

        $processedData['story']['edm:agent'] = $this->mergeValues(
            $processedData['story']['edm:agent'] ?? null,
            $agentData
        );
    }

    private function processWebResource(array $item, array &$processedData): void
    {
        if (isset($item['dcterms:isReferencedBy']['@id'])) {
            $manifestUrl = $item['dcterms:isReferencedBy']['@id'];
            if ($this->isValidManifestUrl($manifestUrl)) {
                $processedData['manifestUrl'] = $manifestUrl;
            }
        }

        if (isset($item['@id'])) {
            if (isset($item['ebucore:hasMimeType']) &&
                str_contains($item['ebucore:hasMimeType'], 'application/pdf')) {
                $processedData['pdfImage'] = $item['@id'];
            } else {
                $processedData['imageLinks'][] = $item['@id'];
            }
        }
    }

    private function processProvidedCHO(array $item, array &$processedData): void
    {
        if (!isset($item['@id'])) return;

        $processedData['externalRecordId'] = $item['@id'];
        $parts = explode('/', $item['@id']);
        $end = end($parts);
        $secondEnd = prev($parts);
        $processedData['recordId'] = '/' . $secondEnd . '/' . $end;
    }

    private function isValidManifestUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
