<?php

namespace App\Services\Converter\DTO;

readonly class PageXmlPageData
{
    public function __construct(
        public string $id,
        public string $fileIdentifier,
        public string $fileName,
        public int $order,
        public int $width,
        public int $height,
        public ?string $plainText = null,
        public ?string $htmlText = null,
    ) {}
}
