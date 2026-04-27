<?php

namespace App\Services\Import;

use App\Models\Item;
use App\Models\Story;
use App\Services\Import\DTO\ParsedJsonLdData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeiImporter
{
    public function __construct(
        private readonly IiifManifestClient $manifestClient,
        private readonly RawImportStorage $rawImportStorage,
    ) {
    }

    public function import(
        ParsedJsonLdData $parsed,
        int $projectId,
        int $datasetId,
        string $importName,
        string $rawBody,
    ): string {
        $recordId = $parsed->recordId;

        DB::transaction(function () use ($parsed, $projectId, $datasetId, $importName) {
            $existing = Story::where('RecordId', $parsed->recordId)->first();

            $storyData = $this->toStoryData($parsed, $projectId, $datasetId, $importName);

            if ($existing === null) {
                $story = $this->buildStory($storyData);
                $story->save();

                $this->importItemsFromJsonLd($story, $parsed);
                return;
            }

            // Keep current DEI behavior: update story only, leave items untouched.
            $story = $this->buildStory($storyData, $existing);
            $story->save();
        });

        $this->rawImportStorage->store($importName, $recordId, $rawBody);

        return $parsed->externalRecordId;
    }

    private function toStoryData(
        ParsedJsonLdData $parsed,
        int $projectId,
        int $datasetId,
        string $importName,
    ): array {
        $field = $parsed->fields;

        return [
            'ExternalRecordId' => $parsed->externalRecordId,
            'RecordId' => $parsed->recordId,
            'ImportName' => $importName,
            'DatasetId' => $datasetId,
            'ProjectId' => $projectId,
            'PlaceUserGenerated' => true,
            'PlaceName' => $field['PlaceName'] ?? null,
            'PlaceLatitude' => $field['PlaceLatitude'] ?? null,
            'PlaceLongitude' => $field['PlaceLongitude'] ?? null,
            'Dc' => [
                'Title' => $field['dc:title'] ?? null,
                'Description' => $field['dc:description'] ?? null,
                'Creator' => $field['dc:creator'] ?? null,
                'Source' => $field['dc:source'] ?? null,
                'Contributor' => $field['dc:contributor'] ?? null,
                'Publisher' => $field['dc:publisher'] ?? null,
                'Coverage' => $field['dc:coverage'] ?? null,
                'Date' => $field['dc:date'] ?? null,
                'Type' => $field['dc:type'] ?? null,
                'Relation' => $field['dc:relation'] ?? null,
                'Rights' => $field['dc:rights'] ?? null,
                'Language' => $field['dc:language'] ?? null,
                'Identifier' => $field['dc:identifier'] ?? null,
            ],
            'Dcterms' => [
                'Medium' => $field['dcterms:medium'] ?? null,
                'Created' => $field['dcterms:created'] ?? null,
                'Provenance' => $field['dcterms:provenance'] ?? null,
            ],
            'Edm' => [
                'LandingPage' => $field['edm:landingPage'] ?? null,
                'Country' => $field['edm:country'] ?? null,
                'DataProvider' => $field['edm:dataProvider'] ?? null,
                'Provider' => $field['edm:provider'] ?? null,
                'Rights' => $field['edm:rights'] ?? null,
                'Year' => $field['edm:year'] ?? null,
                'DatasetName' => $field['edm:datasetName'] ?? null,
                'Begin' => $field['edm:begin'] ?? null,
                'End' => $field['edm:end'] ?? null,
                'IsShownAt' => $field['edm:isShownAt'] ?? null,
                'Language' => $field['edm:language'] ?? null,
                'Agent' => $field['edm:agent'] ?? null,
            ],
        ];
    }

    private function buildStory(array $storyData, ?Story $existing = null): Story
    {
        $story = $existing ?? new Story();

        $story->fill($storyData);
        $story->ExternalRecordId = $storyData['ExternalRecordId'] ?? null;
        $story->RecordId = $storyData['RecordId'] ?? null;
        $story->ImportName = $storyData['ImportName'] ?? null;
        $story->dc = $storyData['Dc'] ?? [];
        $story->dcterms = $storyData['Dcterms'] ?? [];
        $story->edm = $storyData['Edm'] ?? [];

        return $story;
    }

    private function importItemsFromJsonLd(Story $story, ParsedJsonLdData $parsed): void
    {
        $storyTitle = $parsed->fields['dc:title'] ?? '';
        $manifestUrl = $parsed->manifestUrl;
        $manifestConverted = $parsed->manifestConverted;
        $pdfImage = $parsed->pdfImage;

        if ($manifestUrl === '') {
            $this->importItems([[
                'Title' => trim($storyTitle) . ' Item 1',
                'ImageLink' => '',
                'OrderIndex' => 1,
                'Manifest' => '',
            ]], $story);

            return;
        }

        $manifest = $this->manifestClient->fetch($manifestUrl, $manifestConverted, $pdfImage);
        $canvases = $manifest['canvases'];
        $imageLinks = $manifest['imageLinks'];

        $items = [];

        foreach ($canvases as $index => $canvas) {
            $imageLink = data_get($canvas, 'images.0.resource', '');

            $items[] = [
                'Title' => trim($storyTitle) . ' Item ' . ($index + 1),
                'ImageLink' => json_encode($imageLink),
                'OrderIndex' => $index + 1,
                'Manifest' => $manifestUrl,
                'edm:WebResource' => $imageLinks[$index] ?? '',
            ];

            if ($index === 0) {
                $story->PreviewImage = $imageLink;
                $story->save();
            }
        }

        $this->importItems($items, $story);
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
}
