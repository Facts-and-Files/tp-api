<?php

namespace App\Services\Import;

use App\Models\Dataset;
use App\Models\Item;
use App\Models\Project;
use App\Models\Story;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Importer
{
    public function __construct(
        private readonly IiifManifestClient $manifestClient,
    ) {}

    public function importAll(array $data): array
    {
        $inserted = [];
        $errors = [];

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

    public function importFromJsonLd(
        array  $parsed,
        int    $projectId,
        int    $datasetId,
        string $importName,
        string $rawBody,
    ): string {
        $recordId = $parsed['recordId'];

        DB::transaction(function () use ($parsed, $projectId, $datasetId, $importName) {
            $existing = Story::where('RecordId', $parsed['recordId'])->first();

            $storyData = $this->parsedToStoryData($parsed, $projectId, $datasetId, $importName);

            if ($existing === null) {
                $story = $this->buildStory($storyData);
                $story->save();
                $this->importItemsFromJsonLd($story, $parsed);
            } else {
                // Java behaviour: update Story only, leave existing Items untouched
                $story = $this->buildStory($storyData, $existing);
                $story->save();
            }
        });

        $this->saveRawImport($importName, $recordId, $rawBody);

        return $parsed['externalRecordId'];
    }

    private function importStory(array $import, $validProjectIds, $validDatasetIds): array
    {
        $validator = Validator::make($import, [
            'Story.Dc.Title' => 'required',
            'Story.RecordId' => 'required',
            'Items' => 'array',
        ]);

        if ($validator->fails()) {
            return ['error' => $this->storyError(
                $import['Story']['ExternalRecordId'] ?? null,
                $import['Story']['RecordId'] ?? null,
                $import['Story']['Dc']['Title'] ?? null,
                $validator->errors()->all(),
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
                        'StoryId' => $story->StoryId,
                        'ExternalRecordId' => $story->ExternalRecordId,
                        'RecordId' => $story->RecordId,
                        'dc:title' => $story->Dc['Title'] ?? null,
                    ],
                ];
            });
        } catch (ValidationException $ve) {
            return ['error' => $this->storyError(
                $import['Story']['ExternalRecordId'] ?? null,
                $import['Story']['RecordId'] ?? null,
                $import['Story']['Dc']['Title'] ?? null,
                $ve->errors(),
            )];
        } catch (\Exception $e) {
            return ['error' => $this->storyError(
                $import['Story']['ExternalRecordId'] ?? null,
                $import['Story']['RecordId'] ?? null,
                $import['Story']['Dc']['Title'] ?? null,
                [$e->getMessage()],
            )];
        }
    }

    private function buildStory(array $storyData, ?Story $existing = null): Story
    {
        $story = $existing ?? new Story();
        $story->fill($storyData);
        $story->ExternalRecordId = $storyData['ExternalRecordId'] ?? null;
        $story->RecordId         = $storyData['RecordId']         ?? null;
        $story->ImportName       = $storyData['ImportName']       ?? null;
        $story->dc               = $storyData['Dc']               ?? [];
        $story->dcterms          = $storyData['Dcterms']          ?? [];
        $story->edm              = $storyData['Edm']              ?? [];

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
                'Title' => 'required',
                'ImageLink' => 'nullable|string',
                'OrderIndex' => 'integer',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'Items' => $validator->errors()->all(),
                ]);
            }

            $item = new Item();
            $item->fill($itemData);
            $item->StoryId = $story->StoryId;
            $item->ProjectItemId = $itemData['ProjectItemId'] ?? null;
            $item->save();
        }
    }

    private function storyError(
        ?string $externalRecordId,
        ?string $recordId,
        ?string $title,
        array|object $error,
    ): array {
        return [
            'source'           => 'Story',
            'ExternalRecordId' => $externalRecordId,
            'RecordId'         => $recordId,
            'dc:title'         => $title,
            'error'            => $error,
        ];
    }

    // =========================================================================
    // New private helpers for the JSON-LD path
    // =========================================================================

    /**
     * Convert the flat parser output into the same storyData shape
     * that buildStory() already expects.
     */
    private function parsedToStoryData(
        array  $parsed,
        int    $projectId,
        int    $datasetId,
        string $importName,
    ): array {
        $f = $parsed['fields'];

        return [
            'ExternalRecordId' => $parsed['externalRecordId'],
            'RecordId'         => $parsed['recordId'],
            'ImportName'       => $importName,
            'DatasetId'        => $datasetId,
            'ProjectId'        => $projectId,
            'PlaceUserGenerated' => true,
            'PlaceName'        => $f['PlaceName']      ?? null,
            'PlaceLatitude'    => $f['PlaceLatitude']  ?? null,
            'PlaceLongitude'   => $f['PlaceLongitude'] ?? null,
            'Dc' => [
                'Title'       => $f['dc:title']       ?? null,
                'Description' => $f['dc:description'] ?? null,
                'Creator'     => $f['dc:creator']     ?? null,
                'Source'      => $f['dc:source']      ?? null,
                'Contributor' => $f['dc:contributor'] ?? null,
                'Publisher'   => $f['dc:publisher']   ?? null,
                'Coverage'    => $f['dc:coverage']    ?? null,
                'Date'        => $f['dc:date']        ?? null,
                'Type'        => $f['dc:type']        ?? null,
                'Relation'    => $f['dc:relation']    ?? null,
                'Rights'      => $f['dc:rights']      ?? null,
                'Language'    => $f['dc:language']    ?? null,
                'Identifier'  => $f['dc:identifier']  ?? null,
            ],
            'Dcterms' => [
                'Medium'     => $f['dcterms:medium']     ?? null,
                'Created'    => $f['dcterms:created']    ?? null,
                'Provenance' => $f['dcterms:provenance'] ?? null,
            ],
            'Edm' => [
                'LandingPage'  => $f['edm:landingPage']  ?? null,
                'Country'      => $f['edm:country']      ?? null,
                'DataProvider' => $f['edm:dataProvider'] ?? null,
                'Provider'     => $f['edm:provider']     ?? null,
                'Rights'       => $f['edm:rights']       ?? null,
                'Year'         => $f['edm:year']         ?? null,
                'DatasetName'  => $f['edm:datasetName']  ?? null,
                'Begin'        => $f['edm:begin']        ?? null,
                'End'          => $f['edm:end']          ?? null,
                'IsShownAt'    => $f['edm:isShownAt']    ?? null,
                'Language'     => $f['edm:language']     ?? null,
                'Agent'        => $f['edm:agent']        ?? null,
            ],
        ];
    }

    private function importItemsFromJsonLd(Story $story, array $parsed): void
    {
        $storyTitle        = $parsed['fields']['dc:title'] ?? '';
        $manifestUrl       = $parsed['manifestUrl'];
        $manifestConverted = $parsed['manifestConverted'];
        $pdfImage          = $parsed['pdfImage'];

        if ($manifestUrl === '') {
            $this->importItems([[
                'Title'      => trim($storyTitle) . ' Item 1',
                'ImageLink'  => '',
                'OrderIndex' => 1,
                'Manifest'   => '',
            ]], $story);
            return;
        }

        $manifest   = $this->manifestClient->fetch($manifestUrl, $manifestConverted, $pdfImage);
        $canvases   = $manifest['canvases'];
        $imageLinks = $manifest['imageLinks'];

        $items = [];
        foreach ($canvases as $index => $canvas) {
            $imageLink = data_get($canvas, 'images.0.resource', '');

            $items[] = [
                'Title'           => trim($storyTitle) . ' Item ' . ($index + 1),
                'ImageLink'       => json_encode($imageLink),
                'OrderIndex'      => $index + 1,
                'Manifest'        => $manifestUrl,
                'edm:WebResource' => $imageLinks[$index] ?? '',
            ];

            if ($index === 0) {
                $story->PreviewImage = $imageLink;
                $story->save();
            }
        }

        $this->importItems($items, $story);
    }

    private function saveRawImport(string $importName, string $recordId, string $rawBody): void
    {
        $safeRecord = str_replace('/', '_', ltrim($recordId, '/'));
        $path = "{$importName}/{$safeRecord}.json";
        Storage::disk('imports')->put($path, $rawBody);
    }
}
