<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImportResource;
use App\Services\ImportService;
use Illuminate\Http\Request;

class ImportController extends ResponseController
{
    public function __construct(private ImportService $importService) {}

    public function import(Request $request)
    {
        $data = $request->all();

        if (empty($data) || !array_is_list($data)) {
            return $this->sendError('Invalid data', 'Payload must be a non-empty array.', 400);
        }

        [$inserted, $errors] = $this->importService->importAll($data);

        $insertedResource = new ImportResource($inserted);
        $insertedCount    = count($inserted);
        $errorsCount      = count($errors);

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
}
