<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Http\Resources\ImportResource;
use App\Models\Item;
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
                'Items' => 'array',
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

                if (isset($import['Items']) && is_array($import['Items'])) {
                    foreach ($import['Items'] as $itemData) {

                        $itemValidator = Validator::make($itemData, [
                            'Title' => 'required',
                            'ImageLink' => 'required',
                            'OrderIndex' => 'integer'
                        ]);

                        if ($itemValidator->fails()) {
                            $errors[] = [
                                'ExternalRecordId' => $import['Story']['ExternalRecordId'] ?? null,
                                'RecordId'         => $import['Story']['RecordId'] ?? null,
                                'ItemOrderIndex'   => $itemData['OrderIndex'] ?? null,
                                'ProjectItemId'    => $itemData['ProjectItemId'] ?? null,
                                'error'            => $itemValidator->errors()->all()
                            ];

                            continue;
                        }

                        $item = new Item();
                        // fill non-guarded
                        $item->fill($itemData);
                        // fill guarded
                        $item->StoryId = $story->StoryId;
                        $item->ProjectItemId = $itemData['ProjectItemId'] ?? null;
                        $item->save();
                    }
                }

                $inserted[] = [
                    'StoryId'          => $story->StoryId,
                    'ExternalRecordId' => $story->ExternalRecordId,
                    'RecordId'         => $story->RecordId,
                    'dc:title'         => $story->Dc['Title']
                ];

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
