<?php

namespace App\Services\Converter\DTO;

class PageXmlPageData
{
    public function __construct(
        readonly public string $id,
        readonly public string $fileIdentifier,
        readonly public string $fileName,
        readonly public int $order,
        readonly public int $width,
        readonly public int $height,
        readonly public ?string $plainText = null,
        readonly public ?string $htmlText = null,
    ) {
    }
}
