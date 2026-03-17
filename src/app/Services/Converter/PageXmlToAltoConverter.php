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
        $baseRoot = $baseDom->documentElement;
        $remoteRoot = $remoteDom->documentElement;

        if (!$baseRoot || !$remoteRoot) {
            return;
        }

        foreach ($baseRoot->attributes as $attr) {
            $remoteRoot->setAttribute($attr->nodeName, $attr->nodeValue);
        }

        // --- Description: MeasurementUnit + sourceImageInformation ---
        $baseDescription = $this->findFirstChildByLocalName($baseRoot, 'Description');
        $remoteDescription = $this->findFirstChildByLocalName($remoteRoot, 'Description');

        if ($baseDescription) {
            if (!$remoteDescription) {
                $remoteDescription = $remoteDom->createElement($baseDescription->tagName);
                $remoteRoot->insertBefore($remoteDescription, $remoteRoot->firstChild);
            }

            // remove existing MeasurementUnit/sourceImageInformation
            $this->removeChildrenByLocalName($remoteDescription, 'MeasurementUnit');
            $this->removeChildrenByLocalName($remoteDescription, 'sourceImageInformation');

            // import from base
            foreach ($baseDescription->childNodes as $child) {
                if (!($child instanceof DOMElement)) {
                    continue;
                }

                if (!in_array($child->localName, ['MeasurementUnit', 'sourceImageInformation'], true)) {
                    continue;
                }

                $imported = $remoteDom->importNode($child, true);
                $remoteDescription->appendChild($imported);
            }
        }

        // --- Page + PrintSpace attributes from base ---
        $baseLayout = $this->findFirstChildByLocalName($baseRoot, 'Layout');
        $remoteLayout = $this->findFirstChildByLocalName($remoteRoot, 'Layout');

        if ($baseLayout && $remoteLayout) {
            $basePage = $this->findFirstChildByLocalName($baseLayout, 'Page');
            $remotePage = $this->findFirstChildByLocalName($remoteLayout, 'Page');

            if ($basePage && $remotePage) {
                foreach ($basePage->attributes as $attr) {
                    $remotePage->setAttribute($attr->nodeName, $attr->nodeValue);
                }

                $basePrintSpace = $this->findFirstChildByLocalName($basePage, 'PrintSpace');
                $remotePrintSpace = $this->findFirstChildByLocalName($remotePage, 'PrintSpace');

                if ($basePrintSpace && $remotePrintSpace) {
                    foreach ($basePrintSpace->attributes as $attr) {
                        $remotePrintSpace->setAttribute($attr->nodeName, $attr->nodeValue);
                    }
                }
            }
        }
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
