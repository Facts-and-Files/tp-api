<?php

namespace App\Services\Converter;

use App\Services\Converter\DTO\PageXmlPageData;
use DOMDocument;

interface PageXmlConverterInterface
{
    public function convert(string $data, PageXmlPageData $pageData): DOMDocument;
}
