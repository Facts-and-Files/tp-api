<?php

namespace App\Services\Converter;

use App\Services\Converter\DTO\PageXmlPageData;
use DOMDocument;
use DOMElement;

abstract class AbstractPageXmlConverter implements PageXmlConverterInterface
{
    private const PAGE_NAMESPACE = 'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15';
    private const PAGE_SCHEMA_LOCATION = 'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15/pagecontent.xsd';

    abstract public function convert(string $data, PageXmlPageData $pageData): DOMDocument;

    protected function createBasePageDocument(PageXmlPageData $pageData): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElementNS(self::PAGE_NAMESPACE, 'PcGts');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:schemaLocation', self::PAGE_NAMESPACE . ' ' . self::PAGE_SCHEMA_LOCATION);

        $metadata = $dom->createElement('Metadata');
        $creator = $dom->createElement('Creator', 'Transcribathon API Exporter');
        $metadata->appendChild($creator);
        $root->appendChild($metadata);

        $page = $dom->createElement('Page');
        $page->setAttribute('imageFilename', $pageData->fileName);
        $page->setAttribute('imageWidth', (string) max((int) $pageData->width, 1000));
        $page->setAttribute('imageHeight', (string) max((int) $pageData->height, 2000));
        $root->appendChild($page);

        $dom->appendChild($root);

        return $dom;
    }

    protected function createCoords(DOMDocument $dom, int $left, int $top, int $right, int $bottom): DOMElement
    {
        $coords = $dom->createElement('Coords');
        $coords->setAttribute(
            'points',
            sprintf('%d,%d %d,%d %d,%d %d,%d', $left, $top, $right, $top, $right, $bottom, $left, $bottom),
        );

        return $coords;
    }

    protected function createTextEquiv(DOMDocument $dom, string $text): DOMElement
    {
        $textEquiv = $dom->createElement('TextEquiv');
        $unicode = $dom->createElement('Unicode');
        $unicode->appendChild($dom->createTextNode($text));
        $textEquiv->appendChild($unicode);

        return $textEquiv;
    }

    protected function appendReadingOrder(DOMDocument $dom, DOMElement $pageElement, array $regionIds): void
    {
        if ($regionIds === []) {
            return;
        }

        $readingOrder = $dom->createElement('ReadingOrder');
        $orderedGroup = $dom->createElement('OrderedGroup');
        $orderedGroup->setAttribute('id', 'ro_1');

        foreach ($regionIds as $index => $regionId) {
            $regionRef = $dom->createElement('RegionRefIndexed');
            $regionRef->setAttribute('index', (string) $index);
            $regionRef->setAttribute('regionRef', $regionId);
            $orderedGroup->appendChild($regionRef);
        }

        $readingOrder->appendChild($orderedGroup);
        $pageElement->appendChild($readingOrder);
    }
}
