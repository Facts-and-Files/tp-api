<?php

namespace App\Traits;

trait ExtractIiifImageLink
{
    protected function extractIiifImageLink(mixed $iiifImageData): string
    {
        if (!$iiifImageData) {
            return '';
        }

        $imageData = is_array($iiifImageData)
            ? $iiifImageData
            : json_decode(str_replace('\"', '"', $iiifImageData), true);

        return $this->extractIiifImageLinkFromArray($imageData);
    }

    protected function extractIiifImageLinkFromArray(array $iiifImageDataArray): string
    {
        $link = !empty($iiifImageDataArray) ? $iiifImageDataArray['@id'] : '';

        return ($link !== '' && !str_starts_with($link, 'http'))
            ? "https://{$link}"
            : $link;
    }
}
