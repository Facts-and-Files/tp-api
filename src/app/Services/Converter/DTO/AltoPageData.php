<?php

namespace App\Services\Converter\DTO;

class AltoPageData
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $fileIdentifier,
        public readonly string $fileName,
        public readonly int $order,
        public readonly int $width,
        public readonly int $height,
    ) {}
}
