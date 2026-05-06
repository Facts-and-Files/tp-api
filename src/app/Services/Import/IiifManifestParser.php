<?php

namespace App\Services\Import;

class IiifManifestParser
{
    public function parse(array $manifest, string $pdfImage = ''): array
    {
        $canvases = $this->extractCanvases($manifest);

        return [
            'canvases'   => $canvases,
            'imageLinks' => $this->extractImageLinks($canvases, $pdfImage),
        ];
    }

    private function extractCanvases(array $manifest): array
    {
        if ($this->isV3($manifest)) {
            return data_get($manifest, 'items', []);
        }

        // v2: canvases live under sequences[0]
        return data_get($manifest, 'sequences.0.canvases', []);
    }

    private function extractImageLinks(array $canvases, string $pdfImage): array
    {
        if ($pdfImage !== '') {
            return array_map(
                fn(int $i) => $pdfImage . '?page=' . $i,
                range(0, count($canvases) - 1),
            );
        }

        return array_map(
            fn(array $canvas) => $this->resolveImageUrl($canvas),
            $canvases,
        );
    }

    private function resolveImageUrl(array $canvas): string
    {
        // v3: canvas.items[0].items[0].body
        $body = data_get($canvas, 'items.0.items.0.body');

        if (is_array($body)) {
            // SpecificResource wraps the actual resource in 'source'
            if (($body['type'] ?? '') === 'SpecificResource') {
                return data_get($body, 'source.id', '');
            }

            return $body['id'] ?? '';
        }

        // v2: canvas.images[0].resource.@id
        return data_get($canvas, 'images.0.resource.@id', '');
    }

    private function isV3(array $manifest): bool
    {
        $context = $manifest['@context'] ?? '';

        if (is_array($context)) {
            $context = end($context);
        }

        return str_contains((string) $context, 'iiif.io/api/presentation/3');
    }
}
