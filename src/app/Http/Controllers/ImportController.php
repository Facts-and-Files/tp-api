<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeiImportRequest;
use App\Http\Resources\ImportResource;
use App\Services\Import\Importer;
/* use App\Services\Import\DeiImporter; */
use App\Services\Import\JsonLdParser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ImportController extends ResponseController
{
    public function __construct(
        private readonly Importer $importer,
        private readonly JsonLdParser $parser,
    ) {}

    public function import(Request $request): JsonResponse
    {
        $data = $request->all();

        if (empty($data) || !array_is_list($data)) {
            return $this->sendError('Invalid data', 'Payload must be a non-empty array.', 400);
        }

        [$inserted, $errors] = $this->importer->importAll($data);

        $insertedResource = new ImportResource($inserted);
        $insertedCount = count($inserted);
        $errorsCount = count($errors);

        if ($errorsCount > 0 && $insertedCount > 0) {
            return $this->sendPartlyResponse($insertedResource, $errors, 'Import could only be partially inserted.');
        }

        if ($errorsCount > 0) {
            return $this->sendError('Invalid data', $errors, 400);
        }

        if ($insertedCount === 0) {
            return $this->sendError('Invalid data', 'Nothing imported.', 400);
        }

        return $this->sendResponse($insertedResource, 'Import successfully inserted.');
    }

    public function importFromDei(DeiImportRequest $request): JsonResponse
    {
        $parsed = $this->parser->parse(
            $request->input('@graph'),
            $request->input('iiif_url'),
        );

        if (!$parsed->hasRecordId()) {
            return $this->sendError(
                'Invalid data',
                'Could not extract RecordId from payload.',
                422,
            );
        }

        try {
            $externalRecordId = $this->importer->importFromJsonLd(
                parsed: $parsed,
                projectId: (int) $request->input('projectId'),
                datasetId: (int) $request->input('datasetId'),
                importName: (string) $request->input('importName'),
                rawBody: (string) json_encode($request->all()),
            );
        } catch (RuntimeException $e) {
            return $this->sendError('Import failed', $e->getMessage(), 400);
        }

        $resource = new ImportResource([
            'ExternalRecordId' => $externalRecordId,
        ]);

        return $this->sendResponse($resource, 'Story imported successfully.');
    }
}
