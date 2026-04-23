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
        $f = $parsed->fields;

        return [
            'ExternalRecordId' => $parsed->externalRecordId,
            'RecordId' => $parsed->recordId,
            'ImportName' => $importName,
            'DatasetId' => $datasetId,
            'ProjectId' => $projectId,
            'PlaceUserGenerated' => true,
            'PlaceName' => $f['PlaceName'] ?? null,
            'PlaceLatitude' => $f['PlaceLatitude'] ?? null,
            'PlaceLongitude' => $f['PlaceLongitude'] ?? null,
            'Dc' => [
                'Title' => $f['dc:title'] ?? null,
                'Description' => $f['dc:description'] ?? null,
                'Creator' => $f['dc:creator'] ?? null,
                'Source' => $f['dc:source'] ?? null,
                'Contributor' => $f['dc:contributor'] ?? null,
                'Publisher' => $f['dc:publisher'] ?? null,
                'Coverage' => $f['dc:coverage'] ?? null,
                'Date' => $f['dc:date'] ?? null,
                'Type' => $f['dc:type'] ?? null,
                'Relation' => $f['dc:relation'] ?? null,
                'Rights' => $f['dc:rights'] ?? null,
                'Language' => $f['dc:language'] ?? null,
                'Identifier' => $f['dc:identifier'] ?? null,
            ],
            'Dcterms' => [
                'Medium' => $f['dcterms:medium'] ?? null,
                'Created' => $f['dcterms:created'] ?? null,
                'Provenance' => $f['dcterms:provenance'] ?? null,
            ],
            'Edm' => [
                'LandingPage' => $f['edm:landingPage'] ?? null,
                'Country' => $f['edm:country'] ?? null,
                'DataProvider' => $f['edm:dataProvider'] ?? null,
                'Provider' => $f['edm:provider'] ?? null,
                'Rights' => $f['edm:rights'] ?? null,
                'Year' => $f['edm:year'] ?? null,
                'DatasetName' => $f['edm:datasetName'] ?? null,
                'Begin' => $f['edm:begin'] ?? null,
                'End' => $f['edm:end'] ?? null,
                'IsShownAt' => $f['edm:isShownAt'] ?? null,
                'Language' => $f['edm:language'] ?? null,
                'Agent' => $f['edm:agent'] ?? null,
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
