<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\Project;
use App\Http\Resources\ImportResource;
use App\Services\Import\Importer;
use App\Services\Import\JsonLdParser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ImportController extends ResponseController
{
    public function __construct(
        private readonly JsonLdParser $parser,
        private Importer $importer,
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

    public function importFromDei(Request $request): JsonResponse
    {
        $request->validate([
            '@graph' => ['required', 'array', 'min:1'],
            'iiif_url' => ['nullable', 'string', 'url'],
        ]);

        $importName = (string) $request->query('importName', '');
        $datasetId = (int) $request->query('datasetId');
        $projectId = (int) $request->query('projectId');

        if ($importName === '' || $datasetId === 0 || $projectId === 0) {
            return $this->sendError(
                'Invalid data',
                'projectId, importName and datasetId query parameters are required.',
                422,
            );
        }

        if (!Project::find($projectId)) {
            return $this->sendError(
                'Invalid data',
                'The selected projectId is invalid.',
                422,
            );
        }

        if (!Dataset::find($datasetId)) {
            return $this->sendError(
                'Invalid data',
                'The selected datasetId is invalid.',
                422,
            );
        }

        $parsed = $this->parser->parse(
            $request->input('@graph'),
            $request->input('iiif_url') ?? null,
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
                projectId: $projectId,
                datasetId: $datasetId,
                importName: $importName,
                rawBody: (string) json_encode($request->all()),
            );
        } catch (RuntimeException $e) {
            return $this->sendError('Import failed', $e->getMessage(), 400);
        }

        $resource = new ImportResource(['ExternalRecordId' => $externalRecordId]);

        return $this->sendResponse($resource, 'Story imported successfully.');
    }
}
