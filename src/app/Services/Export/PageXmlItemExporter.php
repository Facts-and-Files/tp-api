<?php

namespace App\Services\Export;

use App\Models\Item;
use App\Services\Converter\PageXmlConverterInterface;
use App\Services\Converter\DTO\PageXmlPageData;
use App\Services\ExportCache\FileExportCache;
use App\Traits\ExtractIiifImageLink;
use DOMDocument;

class PageXmlItemExporter
{
    use ExtractIiifImageLink;

    private const EXPORT_FORMAT = 'pagexml';

    public function __construct(
        private readonly FileExportCache $cache,
    ) {
    }

    public function exportWithConverter(Item $item, PageXmlConverterInterface $converter): string
    {
        if ($cached = $this->cache->getCached($item, self::EXPORT_FORMAT)) {
            return $cached;
        }

        $pageXml = $this->generatePageXml($item, $converter)->saveXML();
        $this->cache->put($item, self::EXPORT_FORMAT, $pageXml);

        return $pageXml;
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

    private function generatePageXml(Item $item, PageXmlConverterInterface $converter): DOMDocument
    {
        $iiifImageInfoClean = str_replace('\\"', '"', $item->ImageLink);
        $iiifImageInfo = json_decode($iiifImageInfoClean, true);

        $transcription = $item->Transcription;
        $htmlText = $transcription['Text'] ?? '';
        $plainText = $transcription['TextNoTags'] ?? strip_tags($htmlText);

        $pageData = new PageXmlPageData(
            id: (string) $item->ItemId,
            fileIdentifier: $this->extractIiifImageLinkFromArray($iiifImageInfo),
            fileName: $iiifImageInfo['service']['@id'] ?? '',
            order: (int) $item->OrderIndex,
            width: (int) ($iiifImageInfo['width'] ?? 0),
            height: (int) ($iiifImageInfo['height'] ?? 0),
            plainText: $plainText,
            htmlText: $htmlText,
        );

        return $converter->convert($htmlText, $pageData);
    }
}
