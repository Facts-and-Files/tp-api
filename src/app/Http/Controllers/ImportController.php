<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\ImportResource;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ImportController extends ResponseController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        $inserted = [];
        $errors = [];

        foreach ($data as $index => $import) {
            $validator = Validator::make($import, [
                'Story.Dc.Title' => 'required',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'ExternalRecordId' => $import['Story']['ExternalRecordId'],
                    'RecordId'         => $import['Story']['RecordId'],
                    'dc:title'         => $import['Story']['Dc']['Title'],
                    'error'            => $validator->errors()->all()
                ];
                continue;
            }

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
                $errors[] = [
                    'ExternalRecordId' => $story->ExternalRecordId,
                    'RecordId'         => $story->RecordId,
                    'dc:title'         => $story->Dc['Title'],
                    'error'            => $exception->getMessage()
                ];
            }
        }

        $insertedResource = new ImportResource($inserted);

        $insertedCount = count($inserted);
        $errorsCount = count($errors);

        if ($insertedCount === 0 && $errorsCount > 0) {
            return $this->sendError('Invalid data', $errors, 400);
        }

        if ($insertedCount > 0 && $errorsCount === 0) {
            return $this->sendResponse($insertedResource, 'Import successfully inserted.');
        }

        if ($insertedCount > 0 && $errorsCount > 0) {
            return $this->sendPartlyResponse($insertedResource, $errors, 'Import could only partially inserted.');
        }
    }
}
