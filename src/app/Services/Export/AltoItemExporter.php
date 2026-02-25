<?php

namespace App\Services\Export;

use App\Models\Item;
use App\Services\Converter\AltoConverterInterface;
use App\Services\Converter\DTO\AltoPageData;
use DOMDocument;

class AltoItemExporter
{
    private const EXPORT_FORMAT = 'alto';

    public function __construct(
        private readonly AltoConverterInterface $converter,
        private readonly FileExportCache $cache,
    ) {}

    public function exportToAlto(Item $item): string
    {
        if ($cached = $this->cache->getCached($item, self::EXPORT_FORMAT)) {
            return $cached;
        }

        $altoXml = $this->generateAlto($item)->saveXML();
        $this->cache->put($item, self::EXPORT_FORMAT, $altoXml);

        return $altoXml;
    }

    private function generateAlto(Item $item): DOMDocument
    {
        $iiifImageInfo = json_decode($item->ImageLink, true);

        $pageData = new AltoPageData(
            id: $item->ItemId,
            fileIdentifier: $this->extractIiifImageLink($iiifImageInfo),
            fileName: $iiifImageInfo['service']['@id'] ?? '',
            order: $item->OrderIndex,
            width: $iiifImageInfo['width'] ?? 0,
            height: $iiifImageInfo['height'] ?? 0,
        );

        $dataToConvert = $item->Transcription['Text'] ?? '';

        return $this->converter->convert($dataToConvert, $pageData);
    }

    private function extractIiifImageLink(array $imageData): string
    {
        $link = $imageData['@id'] ?? '';

        return ($link !== '' && !str_starts_with($link, 'http'))
            ? "https://{$link}"
            : $link;
    }
}
