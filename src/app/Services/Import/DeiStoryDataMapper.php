<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\DTO\ParsedJsonLdData;

final class DeiStoryDataMapper
{
    public function map(
        ParsedJsonLdData $parsed,
        int $projectId,
        int $datasetId,
        string $importName,
    ): array {
        $field = $parsed->fields;

        return [
            'ExternalRecordId' => $parsed->externalRecordId,
            'RecordId' => $parsed->recordId,
            'ImportName' => $importName,
            'DatasetId' => $datasetId,
            'ProjectId' => $projectId,
            'PlaceUserGenerated' => true,
            'PlaceName' => $field['PlaceName'] ?? null,
            'PlaceLatitude' => $field['PlaceLatitude'] ?? null,
            'PlaceLongitude' => $field['PlaceLongitude'] ?? null,
            'Dc' => [
                'Title' => $field['dc:title'] ?? null,
                'Description' => $field['dc:description'] ?? null,
                'Creator' => $field['dc:creator'] ?? null,
                'Source' => $field['dc:source'] ?? null,
                'Contributor' => $field['dc:contributor'] ?? null,
                'Publisher' => $field['dc:publisher'] ?? null,
                'Coverage' => $field['dc:coverage'] ?? null,
                'Date' => $field['dc:date'] ?? null,
                'Type' => $field['dc:type'] ?? null,
                'Relation' => $field['dc:relation'] ?? null,
                'Rights' => $field['dc:rights'] ?? null,
                'Language' => $field['dc:language'] ?? null,
                'Identifier' => $field['dc:identifier'] ?? null,
            ],
            'Dcterms' => [
                'Medium' => $field['dcterms:medium'] ?? null,
                'Created' => $field['dcterms:created'] ?? null,
                'Provenance' => $field['dcterms:provenance'] ?? null,
            ],
            'Edm' => [
                'LandingPage' => $field['edm:landingPage'] ?? null,
                'Country' => $field['edm:country'] ?? null,
                'DataProvider' => $field['edm:dataProvider'] ?? null,
                'Provider' => $field['edm:provider'] ?? null,
                'Rights' => $field['edm:rights'] ?? null,
                'Year' => $field['edm:year'] ?? null,
                'DatasetName' => $field['edm:datasetName'] ?? null,
                'Begin' => $field['edm:begin'] ?? null,
                'End' => $field['edm:end'] ?? null,
                'IsShownAt' => $field['edm:isShownAt'] ?? null,
                'Language' => $field['edm:language'] ?? null,
                'Agent' => $field['edm:agent'] ?? null,
            ],
        ];
    }
}
