<?php

namespace App\Services\Converter;

use App\Services\Converter\DTO\AltoPageData;
use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMElement;
use RuntimeException;

class PageXmlToAltoConverter extends AbstractAltoConverter
{
    public function __construct(
        private readonly string $endpoint,
        private readonly ?string $apiKey = null,
    ) {}

    public function convert(string $pageXml, AltoPageData $pageData): DOMDocument
    {
        $response = Http::withHeaders([
            'Accept' => 'application/xml',
            'Content-Type' => 'application/xml',
            'Authorization' => "Bearer {$this->apiKey}",
        ])
        ->withBody($pageXml, 'application/xml')
        ->post($this->endpoint);

        $response->throw();

        $altoXml = $response->body();

        $remoteDom = new DOMDocument('1.0', 'UTF-8');
        if (!@$remoteDom->loadXML($altoXml)) {
            throw new RuntimeException('Invalid ALTO XML returned from PAGE2ALTO API');
        }

        $baseAlto = $this->createBaseAltoDocument($pageData);

        // add metadata to the remote alto
        $this->mergeBaseIntoRemote($baseAlto, $remoteDom);

        return $remoteDom;
    }

    private function mergeBaseIntoRemote(DOMDocument $baseDom, DOMDocument $remoteDom): void
    {
        $baseRoot   = $baseDom->documentElement;
        $remoteRoot = $remoteDom->documentElement;

        if (!$baseRoot || !$remoteRoot) {
            return;
        }

        $this->mergeRootAttributes($baseRoot, $remoteRoot);
        $this->mergeDescription($remoteDom, $baseRoot, $remoteRoot);
        $this->mergeLayoutAttributes($baseRoot, $remoteRoot);
    }

    private function mergeRootAttributes(DOMElement $baseRoot, DOMElement $remoteRoot): void
    {
        foreach ($baseRoot->attributes as $attr) {
            $remoteRoot->setAttribute($attr->nodeName, $attr->nodeValue);
        }
    }

    private function mergeDescription(
        DOMDocument $remoteDom,
        DOMElement $baseRoot,
        DOMElement $remoteRoot,
    ): void {
        $baseDescription = $this->findFirstChildByLocalName($baseRoot, 'Description');

        if (!$baseDescription) {
            return;
        }

        $remoteDescription = $this->findFirstChildByLocalName($remoteRoot, 'Description')
            ?? $this->createAndPrependElement($remoteDom, $remoteRoot, $baseDescription->tagName);

        $this->removeChildrenByLocalName($remoteDescription, 'MeasurementUnit');
        $this->removeChildrenByLocalName($remoteDescription, 'sourceImageInformation');

        $this->importDescriptionChildren($remoteDom, $baseDescription, $remoteDescription);
    }

    private function importDescriptionChildren(
        DOMDocument $remoteDom,
        DOMElement $baseDescription,
        DOMElement $remoteDescription,
    ): void {
        $importable = ['MeasurementUnit', 'sourceImageInformation'];

        foreach ($baseDescription->childNodes as $child) {
            if (!($child instanceof DOMElement)) {
                continue;
            }

            if (!in_array($child->localName, $importable, true)) {
                continue;
            }

            $remoteDescription->appendChild($remoteDom->importNode($child, true));
        }
    }

    private function mergeLayoutAttributes(DOMElement $baseRoot, DOMElement $remoteRoot): void
    {
        $baseLayout   = $this->findFirstChildByLocalName($baseRoot, 'Layout');
        $remoteLayout = $this->findFirstChildByLocalName($remoteRoot, 'Layout');

        if (!$baseLayout || !$remoteLayout) {
            return;
        }

        $basePage   = $this->findFirstChildByLocalName($baseLayout, 'Page');
        $remotePage = $this->findFirstChildByLocalName($remoteLayout, 'Page');

        if (!$basePage || !$remotePage) {
            return;
        }

        $this->mergeAttributes($basePage, $remotePage);

        $basePrintSpace   = $this->findFirstChildByLocalName($basePage, 'PrintSpace');
        $remotePrintSpace = $this->findFirstChildByLocalName($remotePage, 'PrintSpace');

        if ($basePrintSpace && $remotePrintSpace) {
            $this->mergeAttributes($basePrintSpace, $remotePrintSpace);
        }
    }

    private function mergeAttributes(DOMElement $source, DOMElement $target): void
    {
        foreach ($source->attributes as $attr) {
            $target->setAttribute($attr->nodeName, $attr->nodeValue);
        }
    }

    private function createAndPrependElement(
        DOMDocument $dom,
        DOMElement $parent,
        string $tagName,
    ): DOMElement {
        $element = $dom->createElement($tagName);
        $parent->insertBefore($element, $parent->firstChild);

        return $element;
    }

    private function findFirstChildByLocalName(DOMElement $parent, string $localName): ?DOMElement
    {
        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $localName) {
                return $child;
            }
        }

        return null;
    }

    private function removeChildrenByLocalName(\DOMElement $parent, string $localName): void
    {
        $toRemove = [];
        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $localName) {
                $toRemove[] = $child;
            }
        }
        foreach ($toRemove as $child) {
            $parent->removeChild($child);
        }
    }
}
