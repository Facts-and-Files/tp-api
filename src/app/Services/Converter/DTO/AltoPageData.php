<?php

namespace App\Services\Converter\DTO;

readonly class AltoPageData
{
    public function __construct(
        public int|string $id,
        public string $fileIdentifier,
        public string $fileName,
        public int $order,
        public int $width,
        public int $height,
    ) {}
}
