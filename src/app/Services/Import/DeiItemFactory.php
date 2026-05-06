<?php

namespace App\Services\Import;

use App\Services\Import\DTO\ParsedJsonLdData;
use RuntimeException;

final class DeiItemFactory
{
    public function __construct(
        private readonly IiifManifestClient $manifestClient,
    ) {}

    public function fetchRequiredManifest(ParsedJsonLdData $parsed): array
    {
        if ($parsed->manifestUrl === '') {
            throw new RuntimeException('IIIF manifest missing. Import aborted.');
        }

        $manifest = $this->manifestClient->fetch(
            $parsed->manifestUrl,
            $parsed->pdfImage,
            $parsed->manifestAuthMode,
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
            $imageResource = $this->normaliseImageResource($canvas);

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

    private function normaliseImageResource(array $canvas): array
    {
        $body = data_get($canvas, 'items.0.items.0.body');

        if (is_array($body) && isset($body['id'])) {
            return $this->normaliseV3Body($body);
        }

        // v3 SpecificResource — image URL lives in source.id
        if (is_array($body) && ($body['type'] ?? '') === 'SpecificResource') {
            $source = $body['source'] ?? null;
            return [
                '@id'   => is_array($source) ? ($source['id'] ?? '') : '',
                '@type' => 'dctypes:Image',
            ];
        }

        // v2: already in the correct shape
        return data_get($canvas, 'images.0.resource', []);
    }

    private function normaliseV3Body(array $body): array
    {
        $normalised = array_filter([
            '@id'    => $body['id'],
            '@type'  => 'dctypes:Image',
            'format' => $body['format'] ?? null,
            'height' => $body['height'] ?? null,
            'width'  => $body['width']  ?? null,
        ], fn($v) => $v !== null);

        $service = $body['service'] ?? null;

        if (is_array($service)) {
            $normalised['service'] = array_is_list($service) ? $service[0] : $service;
        } elseif ($service !== null) {
            $normalised['service'] = $service;
        }

        return $normalised;
    }
}
