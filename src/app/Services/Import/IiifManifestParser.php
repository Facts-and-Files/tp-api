<?php

namespace App\Services\Import;

class IiifManifestParser
{
    public function parse(array $manifest, string $pdfImage = ''): array
    {
        $canvases = data_get($manifest, 'sequences.0.canvases', []);

        return [
            'canvases'   => $canvases,
            'imageLinks' => $this->extractImageLinks($canvases, $pdfImage),
        ];
    }

    private function extractImageLinks(array $canvases, string $pdfImage): array
    {
        if ($pdfImage !== '') {
            return array_map(
                fn(int $i) => $pdfImage . '?page=' . $i,
                range(0, count($canvases) - 1)
            );
        }

        return array_map(
            fn(array $canvas) => data_get($canvas, 'images.0.resource.@id', ''),
            $canvases
        );
    }
}
