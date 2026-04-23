<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Models\Story;
use App\Services\Import\DTO\ParsedJsonLdData;

final class DeiItemFactory
{
    public function __construct(private readonly IiifManifestClient $manifestClient)
    {
    }

    public function make(Story $story, ParsedJsonLdData $parsed): array
    {
        if ($parsed->manifestUrl === '') {
            return [[
                'Title' => $this->itemTitle($parsed->storyTitle(), 1),
                'ImageLink' => '',
                'OrderIndex' => 1,
                'Manifest' => '',
            ]];
        }

        $manifest = $this->manifestClient->fetch(
            $parsed->manifestUrl,
            $parsed->manifestConverted,
            $parsed->pdfImage,
        );

        $canvases = $manifest['canvases'];
        $imageLinks = $manifest['imageLinks'];
        $items = [];

        foreach ($canvases as $index => $canvas) {
            $imageLink = data_get($canvas, 'images.0.resource', '');

            $items[] = [
                'Title' => $this->itemTitle($parsed->storyTitle(), $index + 1),
                'ImageLink' => json_encode($imageLink),
                'OrderIndex' => $index + 1,
                'Manifest' => $parsed->manifestUrl,
                'edm:WebResource' => $imageLinks[$index] ?? '',
            ];

            if ($index === 0) {
                $story->PreviewImage = $imageLink;
                $story->save();
            }
        }

        return $items;
    }

    private function itemTitle(string $storyTitle, int $index): string
    {
        $baseTitle = $storyTitle !== '' ? $storyTitle : 'Imported story';

        return trim($baseTitle) . ' Item ' . $index;
    }
}
