<?php

namespace App\Services\Export;

use App\Models\Item;
use App\Services\Converter\AltoConverterInterface;
use App\Services\Converter\DTO\AltoPageData;
use App\Services\ExportCache\FileExportCache;
use App\Traits\ExtractIiifImageLink;
use DOMDocument;

class AltoItemExporter
{
    use ExtractIiifImageLink;

    private const EXPORT_FORMAT = 'alto';

    public function __construct(
        private readonly FileExportCache $cache,
    ) {}

    public function exportWithConverter(Item $item, AltoConverterInterface $converter): string
    {
        if ($cached = $this->cache->getCached($item, self::EXPORT_FORMAT)) {
            return $cached;
        }

        $altoXml = $this->generateAlto($item, $converter)->saveXML();
        $this->cache->put($item, self::EXPORT_FORMAT, $altoXml);

        return $altoXml;
    }

    public function getCached(Item $item): ?string
    {
        $cached = $this->cache->getCached($item, self::EXPORT_FORMAT);

        return is_string($cached) && $cached !== '' ? $cached : null;
    }

    public function hasCached(Item $item): bool
    {
        return $this->getCached($item) !== null;
    }

    private function generateAlto(Item $item, AltoConverterInterface $converter): DOMDocument
    {
        // some included JSON strings needs cleaning
        $iiifImageInfoClean = str_replace('\"', '"', $item->ImageLink);
        $iiifImageInfo = json_decode($iiifImageInfoClean, true);

        $pageData = new AltoPageData(
            id: $item->ItemId,
            fileIdentifier: $this->extractIiifImageLinkFromArray($iiifImageInfo),
            fileName: $iiifImageInfo['service']['@id'] ?? '',
            order: $item->OrderIndex,
            width: $iiifImageInfo['width'] ?? 0,
            height: $iiifImageInfo['height'] ?? 0,
        );

        $dataToConvert = $item->Transcription['Text'] ?? '';

        return $converter->convert($dataToConvert, $pageData);
    }
}
