<?php

namespace App\Services\Import;

use App\Models\Story;
use App\Services\Import\DTO\ParsedJsonLdData;
use RuntimeException;

final class DeiItemFactory
{
    public function __construct(
        private readonly IiifManifestClient $manifestClient,
    ) {
    }

    public function fetchRequiredManifest(ParsedJsonLdData $parsed): array
    {
        if ($parsed->manifestUrl === '') {
            throw new RuntimeException('IIIF manifest missing. Import aborted.');
        }

        $manifest = $this->manifestClient->fetch(
            $parsed->manifestUrl,
            $parsed->manifestConverted,
            $parsed->pdfImage,
        );

        $canvases = $manifest['canvases'] ?? [];

        if (empty($canvases)) {
            throw new RuntimeException('IIIF manifest contains no canvases. Import aborted.');
        }

        return $manifest;
    }

    public function makeFromManifest(
        ParsedJsonLdData $parsed,
        array $manifest,
    ): array {
        $canvases = $manifest['canvases'] ?? [];
        $imageLinks = $manifest['imageLinks'] ?? [];

        if (empty($canvases)) {
            throw new RuntimeException('IIIF manifest contains no canvases. Import aborted.');
        }

        $items = [];
        $previewImage = null;

        foreach ($canvases as $index => $canvas) {
            $imageResource = data_get($canvas, 'images.0.resource', '');

            if ($index === 0) {
                $previewImage = $imageResource;
            }

            $items[] = [
                'Title' => $this->itemTitle($parsed->storyTitle(), $index + 1),
                'ImageLink' => json_encode($imageResource),
                'OrderIndex' => $index + 1,
                'Manifest' => $parsed->manifestUrl,
                'edm:WebResource' => $imageLinks[$index] ?? '',
            ];
        }

        return [
            'previewImage' => $previewImage,
            'items' => $items,
        ];
    }

    private function itemTitle(string $storyTitle, int $index): string
    {
        $baseTitle = $storyTitle !== '' ? $storyTitle : 'Imported story';

        return trim($baseTitle) . ' Item ' . $index;
    }
}
