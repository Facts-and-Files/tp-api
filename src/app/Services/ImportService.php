<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\Item;
use App\Models\Project;
use App\Models\Story;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImportService
{
    public function importAll(array $data): array
    {
        $inserted = [];
        $errors   = [];

        // pre-fetch valid IDs to avoid N+1 queries in the loop
        $projectIds = collect($data)
            ->map(fn($i) => $i['Story']['ProjectId'] ?? null)
            ->filter()->unique();

        $datasetIds = collect($data)
            ->map(fn($i) => $i['Story']['DatasetId'] ?? null)
            ->filter()->unique();

        $validProjectIds = Project::whereIn('ProjectId', $projectIds)->pluck('ProjectId');
        $validDatasetIds = Dataset::whereIn('DatasetId', $datasetIds)->pluck('DatasetId');


        foreach ($data as $import) {
            $result = $this->importStory($import, $validProjectIds, $validDatasetIds);

            if (isset($result['error'])) {
                $errors[] = $result['error'];
            } else {
                $inserted[] = $result['inserted'];
            }
        }

        return [$inserted, $errors];
    }

    private function importStory(array $import, $validProjectIds, $validDatasetIds): array
    {
        $validator = Validator::make($import, [
            'Story.Dc.Title'         => 'required',
            'Story.RecordId'         => 'required',
            'Items'                  => 'array',
        ]);

        if ($validator->fails()) {
            return ['error' => $this->storyError(
                $import['Story']['ExternalRecordId'] ?? null,
                $import['Story']['RecordId'] ?? null,
                $import['Story']['Dc']['Title'] ?? null,
                $validator->errors()->all()
            )];
        }

        try {
            return DB::transaction(function () use ($import, $validProjectIds, $validDatasetIds) {
                $story = $this->buildStory($import['Story']);

                $this->validateForeignKeys($story, $validProjectIds, $validDatasetIds);

                $story->save();

                $this->importItems($import['Items'] ?? [], $story); // throws on failure

                return [
                    'inserted' => [
                        'StoryId'          => $story->StoryId,
                        'ExternalRecordId' => $story->ExternalRecordId,
                        'RecordId'         => $story->RecordId,
                        'dc:title'         => $story->Dc['Title'] ?? null,
                    ],
                ];
            });
        } catch (ValidationException $ve) {
            return ['error' => $this->storyError(
                $import['Story']['ExternalRecordId'] ?? null,
                $import['Story']['RecordId'] ?? null,
                $import['Story']['Dc']['Title'] ?? null,
                $ve->errors()
            )];
        } catch (\Exception $e) {
            return ['error' => $this->storyError(
                $import['Story']['ExternalRecordId'] ?? null,
                $import['Story']['RecordId'] ?? null,
                $import['Story']['Dc']['Title'] ?? null,
                [$e->getMessage()]
            )];
        }
    }

    private function buildStory(array $storyData): Story
    {
        $story = new Story();
        $story->fill($storyData);
        $story->ExternalRecordId = $storyData['ExternalRecordId'] ?? null;
        $story->RecordId         = $storyData['RecordId'] ?? null;
        $story->dc               = $storyData['Dc'] ?? [];
        $story->dcterms          = $storyData['Dcterms'] ?? [];
        $story->edm              = $storyData['Edm'] ?? [];

        return $story;
    }

    private function validateForeignKeys(Story $story, $validProjectIds, $validDatasetIds): void
    {
        if ($story->ProjectId && !$validProjectIds->contains($story->ProjectId)) {
            throw ValidationException::withMessages(['ProjectId' => __('ProjectId does not exist')]);
        }

        if ($story->DatasetId && !$validDatasetIds->contains($story->DatasetId)) {
            throw ValidationException::withMessages(['DatasetId' => __('DatasetId does not exist')]);
        }
    }

    private function importItems(array $items, Story $story): void
    {
        foreach ($items as $itemData) {
            $validator = Validator::make($itemData, [
                'Title'      => 'required',
                'ImageLink'  => 'required',
                'OrderIndex' => 'integer',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'Items' => $validator->errors()->all(),
                ]);
            }

            $item = new Item();
            $item->fill($itemData);
            $item->StoryId       = $story->StoryId;
            $item->ProjectItemId = $itemData['ProjectItemId'] ?? null;
            $item->save();
        }
    }

    private function storyError(
        ?string $externalRecordId,
        ?string $recordId,
        ?string $title,
        array|object $error
    ): array {
        return [
            'source'           => 'Story',
            'ExternalRecordId' => $externalRecordId,
            'RecordId'         => $recordId,
            'dc:title'         => $title,
            'error'            => $error,
        ];
    }
}
