<?php

namespace App\Services;

use App\Models\Dataset;
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

        $processedData = $this->jsonLdProcessor->processJsonLd($data['@graph']);
print_r($processedData);

        // Store import file
        // $this->importStorage->storeImportFile(
        //     $data['ImportName'] ?? 'default_' . date('Y-m-d_H-i-s'),
        //     $data['recordId'],
        //     json_encode(['@graph' => $data['@graph']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        // );

        return ['inserted' => []];
    }
}
