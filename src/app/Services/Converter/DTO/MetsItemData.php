<?php

namespace App\Services\Converter\DTO;

readonly class MetsItemData
{
    public function __construct(
        public string $itemId,
        public int $order,
        public string $imageLink,
        public string $altoXml,
    ) {}
}
