<?php

namespace App\Services\Converter;

use App\Services\Converter\DTO\AltoPageData;
use DOMDocument;

interface AltoConverterInterface
{
    public function convert(string $data, AltoPageData $pageData): DOMDocument;
}
