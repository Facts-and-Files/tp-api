<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\Story;
use Illuminate\Validation\ValidationException;

class DeiImportService
{
    public function __construct(
        private JsonLdProcessorService $jsonLdProcessor,
        private ImportStorageService $importStorage,
    ) {}

    public function import(array $data, int $projectId): array
    {
        if (isset($data['DatasetId']) && !Dataset::find($data['DatasetId'])) {
            throw ValidationException::withMessages(
                ['DatasetId' => 'The DatasetId does not exists.']
            );
        }

        // use @graph here, because DEI wronlgy wraps all meta data (even @context)
        // in @graph root element
        $processedData = $this->jsonLdProcessor->processJsonLd($data['@graph']);

        $storyData = array_merge($processedData['story'], [
            'ProjectId' => $projectId,
            'PlaceUserGenerated' => true,
            'ImportName' => $data['ImportName'] ?? '',
            'DatasetId' => $data['DatasetId'] ?? null,
        ]);

// print_r($storyData);
        $existingStory = Story::where('RecordId', $processedData['RecordId'])->first();
// check RecordId is only generated when ProvidedCHO is existent, see JsonLdProcessorService
// seems not correct
// print_r($existingStory);

        // Store import file
        // $this->importStorage->storeImportFile(
        //     $data['ImportName'] ?? 'default_' . date('Y-m-d_H-i-s'),
        //     $data['recordId'],
        //     json_encode(['@graph' => $data['@graph']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        // );

        return ['inserted' => []];
    }
}
