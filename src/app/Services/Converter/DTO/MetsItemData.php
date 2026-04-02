<?php

namespace App\Services\Converter\DTO;

class MetsItemData
{
    public function __construct(
        public readonly string $itemId,
        public readonly int $order,
        public readonly string $imageLink,
        public readonly string $altoXml,
    ) {}
}
