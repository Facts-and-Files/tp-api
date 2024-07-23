<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\ImportResource;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ImportController extends ResponseController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        $inserted = [];
        $errors = [];

        try {
            $validatedData = $request->validate([
                '*.Story.Dc.Title' => 'required'
            ]);
        } catch (ValidationException $exception) {
            return $this->sendError('Validation error', $exception->errors(), 422);
        }

        foreach ($data as $import) {
            $story = new Story();

            // fill non-guarded
            $story->fill($import['Story']);

            // fill guarded
            $story->ExternalRecordId = $import['Story']['ExternalRecordId'] ?? null;
            $story->RecordId         = $import['Story']['RecordId'] ?? null;

            // fill these with accessor/mutator
            $story->dc      = $import['Story']['Dc'];
            $story->dcterms = $import['Story']['Dcterms'];
            $story->edm     = $import['Story']['Edm'];

            try {
                $story->save();
                $insertedStory = [
                    'StoryId'          => $story->StoryId,
                    'ExternalRecordId' => $story->ExternalRecordId,
                    'RecordId'         => $story->RecordId,
                    'dc:title'         => $story->Dc['Title']
                ];
                $inserted[] = $insertedStory;
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        $insertedResource = new ImportResource($inserted);
        $errorResource = new ImportResource($errors);

        $insertedCount = count($inserted);
        $errorsCount = count($errors);

        if ($insertedCount === 0 && $errorsCount > 0) {
            return $this->sendError('Invalid data', $errors, 400);
        }

        if ($insertedCount > 0 && $errorsCount === 0) {
            return $this->sendResponse($insertedResource, 'Import successfully inserted.');
        }

        if ($insertedCount > 0 && $errorsCount > 0) {
            return $this->sendPartlyResponse($insertedResource, $errorResource, 'Import could only partially inserted.');
        }
    }
}
