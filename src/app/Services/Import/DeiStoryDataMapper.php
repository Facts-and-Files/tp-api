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
        $f = $parsed->fields;

        return [
            'ExternalRecordId' => $parsed->externalRecordId,
            'RecordId' => $parsed->recordId,
            'ImportName' => $importName,
            'DatasetId' => $datasetId,
            'ProjectId' => $projectId,
            'PlaceUserGenerated' => true,
            'PlaceName' => $f['PlaceName'] ?? null,
            'PlaceLatitude' => $f['PlaceLatitude'] ?? null,
            'PlaceLongitude' => $f['PlaceLongitude'] ?? null,
            'Dc' => [
                'Title' => $f['dc:title'] ?? null,
                'Description' => $f['dc:description'] ?? null,
                'Creator' => $f['dc:creator'] ?? null,
                'Source' => $f['dc:source'] ?? null,
                'Contributor' => $f['dc:contributor'] ?? null,
                'Publisher' => $f['dc:publisher'] ?? null,
                'Coverage' => $f['dc:coverage'] ?? null,
                'Date' => $f['dc:date'] ?? null,
                'Type' => $f['dc:type'] ?? null,
                'Relation' => $f['dc:relation'] ?? null,
                'Rights' => $f['dc:rights'] ?? null,
                'Language' => $f['dc:language'] ?? null,
                'Identifier' => $f['dc:identifier'] ?? null,
            ],
            'Dcterms' => [
                'Medium' => $f['dcterms:medium'] ?? null,
                'Created' => $f['dcterms:created'] ?? null,
                'Provenance' => $f['dcterms:provenance'] ?? null,
            ],
            'Edm' => [
                'LandingPage' => $f['edm:landingPage'] ?? null,
                'Country' => $f['edm:country'] ?? null,
                'DataProvider' => $f['edm:dataProvider'] ?? null,
                'Provider' => $f['edm:provider'] ?? null,
                'Rights' => $f['edm:rights'] ?? null,
                'Year' => $f['edm:year'] ?? null,
                'DatasetName' => $f['edm:datasetName'] ?? null,
                'Begin' => $f['edm:begin'] ?? null,
                'End' => $f['edm:end'] ?? null,
                'IsShownAt' => $f['edm:isShownAt'] ?? null,
                'Language' => $f['edm:language'] ?? null,
                'Agent' => $f['edm:agent'] ?? null,
            ],
        ];
    }
}
