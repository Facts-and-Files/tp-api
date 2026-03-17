<?php

namespace App\Services\Converter;

use App\Services\Converter\DTO\AltoPageData;
use DOMDocument;

abstract class AbstractAltoConverter implements AltoConverterInterface
{
    private const ALTO_NAMESPACE = 'http://www.loc.gov/standards/alto/ns-v4#';
    private const ALTO_SCHEMA_LOCATION = 'http://www.loc.gov/standards/alto/v4/alto-4-2.xsd';

    protected string $measurementUnit = 'pixel';

    abstract public function convert(string $data, AltoPageData $pageData): DOMDocument;

    protected function createBaseAltoDocument(AltoPageData $pageData): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElementNS(self::ALTO_NAMESPACE, 'alto');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute(
            'xsi:schemaLocation',
            self::ALTO_NAMESPACE . ' ' . self::ALTO_SCHEMA_LOCATION,
        );

        $description = $dom->createElement('Description');
        $measurementUnit = $dom->createElement('MeasurementUnit');
        $measurementUnit->nodeValue = $this->measurementUnit;
        $description->appendChild($measurementUnit);

        $sourceImageInformation = $dom->createElement('sourceImageInformation');
        $fileName = $dom->createElement('fileName');
        $fileName->nodeValue = $pageData->fileName;
        $sourceImageInformation->appendChild($fileName);

        $fileIdentifier = $dom->createElement('fileIdentifier');
        $fileIdentifier->nodeValue = $pageData->fileIdentifier;
        $sourceImageInformation->appendChild($fileIdentifier);
        $description->appendChild($sourceImageInformation);

        $root->appendChild($description);

        $layout = $dom->createElement('Layout');

        $page = $dom->createElement('Page');
        $page->setAttribute('ID', $pageData->id);
        $page->setAttribute('PHYSICAL_IMG_NR', $pageData->order);
        $page->setAttribute('WIDTH', $pageData->width);
        $page->setAttribute('HEIGHT', $pageData->height);
        $layout->appendChild($page);

        $printSpace = $dom->createElement('PrintSpace');
        $printSpace->setAttribute('WIDTH', $pageData->width);
        $printSpace->setAttribute('HEIGHT', $pageData->height);
        $printSpace->setAttribute('HPOS', '0');
        $printSpace->setAttribute('VPOS', '0');
        $page->appendChild($printSpace);

        $root->appendChild($layout);

        $dom->appendChild($root);

        return $dom;
    }
}
