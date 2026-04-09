<?php

namespace App\Services\Converter;

use App\Services\Converter\DTO\MetsStoryData;
use DOMDocument;
use DOMElement;

class MetsConverter
{
    private const METS_NS = 'http://www.loc.gov/METS/v2';
    private const DC_NS = 'http://purl.org/dc/elements/1.1/';
    private const DCTERMS_NS = 'http://purl.org/dc/terms/';
    private const EDM_NS = 'http://www.europeana.eu/schemas/edm/';
    private const PREMIS_NS = 'http://www.loc.gov/premis/v3';
    private const ALTO_NS = 'http://www.loc.gov/standards/alto/ns-v4#';
    private const METS_AGENT = 'Transcribathon Portal';
    private const METS_AGENT_NOTE = 'Crowd powered Transcriptions and Enrichments of Digital Cultural Heritage Objects.';

    public function convert(MetsStoryData $data): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $this->buildRoot($dom, $data->storyId);
        $root->appendChild($this->buildMetsHdr($dom, $data));
        $root->appendChild($this->buildMdSec($dom, $data));

        $fileSec = $this->buildFileSec($dom, $data);
        if ($fileSec !== null) {
            $root->appendChild($fileSec);
        }

        $root->appendChild($this->buildStructSec($dom, $data));

        $dom->appendChild($root);

        return $dom;
    }

    private function buildRoot(DOMDocument $dom, string $id): DOMElement
    {
        $mets = $dom->createElementNS(self::METS_NS, 'mets:mets');

        $mets->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $mets->setAttribute('xsi:schemaLocation', self::METS_NS . ' https://loc.gov/standards/mets/mets2.xsd');
        $mets->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dc', self::DC_NS);
        $mets->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dcterms', self::DCTERMS_NS);
        $mets->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:edm', self::EDM_NS);
        $mets->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:premis', self::PREMIS_NS);
        $mets->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:alto', self::ALTO_NS);
        // we have no ARK or DOI stored, so we use internal StoryId here
        $mets->setAttribute('OBJID', $id);

        return $mets;
    }

    private function buildMetsHdr(DOMDocument $dom, MetsStoryData $data): DOMElement
    {
        $hdr = $dom->createElement('mets:metsHdr');
        $hdr->setAttribute('CREATEDATE', gmdate('Y-m-d\TH:i:s\Z'));

        $agent = $dom->createElement('mets:agent');
        $agent->setAttribute('TYPE', 'ORGANIZATION');
        $agent->setAttribute('ROLE', 'CREATOR');
        $agent->appendChild($dom->createElement('mets:name', self::METS_AGENT));
        $agent->appendChild($dom->createElement('mets:note', self::METS_AGENT_NOTE));
        $hdr->appendChild($agent);

        // altRecordID should some legacy identifier
        // sometimes we have valid dc:identifier fot the story, sometimes not
        // so we use the story's RecordId, which is external and with type we determine
        // from which project it came
        $altId = $dom->createElement('mets:altRecordID');
        $altId->setAttribute('TYPE', 'External Record ID from ' . $data->projectName);
        $altId->appendChild($dom->createTextNode($data->recordId));
        $hdr->appendChild($altId);

        return $hdr;
    }

    private function buildMdSec(DOMDocument $dom, MetsStoryData $data): DOMElement
    {
        $mdSec = $dom->createElement('mets:mdSec');

        $mdSec->appendChild($this->buildMdWrap($dom, 'MD_DC', 'DESCRIPTIVE', 'DC', function (DOMElement $xmlData) use ($dom, $data) {
            foreach ($data->dc as $key => $value) {
                $el = $dom->createElement('dc:' . lcfirst($key));
                $el->appendChild($dom->createTextNode($value));
                $xmlData->appendChild($el);
            }
        }));

        $mdSec->appendChild($this->buildMdWrap($dom, 'MD_DCTERMS', 'DESCRIPTIVE', 'DCTERMS', function (DOMElement $xmlData) use ($dom, $data) {
            foreach ($data->dcterms as $key => $value) {
                $el = $dom->createElement('dcterms:' . lcfirst($key));
                $el->appendChild($dom->createTextNode($value));
                $xmlData->appendChild($el);
            }
        }));

        $mdSec->appendChild($this->buildMdWrap($dom, 'MD_EDM', 'DESCRIPTIVE', 'EDM', function (DOMElement $xmlData) use ($dom, $data) {
            $fields = ['LandingPage', 'Country', 'DataProvider', 'Provider', 'Year', 'Rights',
                'DatasetName', 'Begin', 'End', 'IsShownAt', 'Language', 'Agent'];
            foreach ($fields as $field) {
                if (!empty($data->edm[$field])) {
                    $el = $dom->createElement('edm:' . lcfirst($field));
                    $el->appendChild($dom->createTextNode(trim($data->edm[$field])));
                    $xmlData->appendChild($el);
                }
            }
        }));

        $mdSec->appendChild($this->buildMdWrap($dom, 'MD_PREMIS_EVENT', 'PROVENANCE', 'PREMIS:EVENT', function (DOMElement $xmlData) use ($dom, $data) {
            $events = [
                'capture' => $data->timestamp,
                'metadata modification' => $data->lastUpdated,
            ];

            foreach ($events as $label => $value) {
                $event = $dom->createElement('premis:event');

                $type = $dom->createElement('premis:eventType');
                $type->appendChild($dom->createTextNode($label));
                $event->appendChild($type);

                $dateTime = $dom->createElement('premis:eventDateTime');
                $dateTime->appendChild($dom->createTextNode($this->toDateTime($value)));
                $event->appendChild($dateTime);

                $xmlData->appendChild($event);
            }
        }));

        $mdSec->appendChild($this->buildMdWrap($dom, 'MD_PREMIS_RIGHTS', 'RIGHTS', 'PREMIS:RIGHTS', function (DOMElement $xmlData) use ($dom, $data) {
            $rights = $dom->createElement('premis:rights');

            $rightsStatement = $dom->createElement('premis:rightsStatement');

            // identifier
            $rightsStatementIdentifier = $dom->createElement('premis:rightsStatementIdentifier');
            $rightsStatementIdentifierType = $dom->createElement('premis:rightsStatementIdentifierType');
            $rightsStatementIdentifierType->appendChild($dom->createTextNode('local'));
            $rightsStatementIdentifier->appendChild($rightsStatementIdentifierType);
            $rightsStatementIdentifierValue = $dom->createElement('premis:rightsStatementIdentifierValue');
            $rightsStatementIdentifierValue->appendChild($dom->createTextNode('rights-001'));
            $rightsStatementIdentifier->appendChild($rightsStatementIdentifierValue);
            $rightsStatement->appendChild($rightsStatementIdentifier);

            // basis
            $rightsBasis = $dom->createElement('premis:rightsBasis');
            $rightsBasis->appendChild($dom->createTextNode('license'));
            $rightsStatement->appendChild($rightsBasis);

            // license information
            if ($data->edm['Rights']) {
                $licenseInformation = $dom->createElement('premis:licenseInformation');
                $licenseDocumentationIdentifier = $dom->createElement('premis:licenseDocumentationIdentifier');
                $licenseDocumentationIdentifierType = $dom->createElement('premis:licenseDocumentationIdentifierType');
                $licenseDocumentationIdentifierType->appendChild($dom->createTextNode('uri'));
                $licenseDocumentationIdentifier->appendChild($licenseDocumentationIdentifierType);
                $licenseDocumentationIdentifierValue = $dom->createElement('premis:licenseDocumentationIdentifierValue');
                $licenseDocumentationIdentifierValue->appendChild($dom->createTextNode(trim($data->edm['Rights'])));
                $licenseDocumentationIdentifier->appendChild($licenseDocumentationIdentifierValue);
                $licenseInformation->appendChild($licenseDocumentationIdentifier);
                $rightsStatement->appendChild($licenseInformation);
            }

            $rights->appendChild($rightsStatement);
            $xmlData->appendChild($rights);
        }));

        $mdForManifest = $this->buildMd($dom, 'MD_IIIF_MANIFEST', 'DESCRIPTIVE');
        $mdRef = $dom->createElement('mets:mdRef');
        $mdRef->setAttribute('MDTYPE', 'IIIF');
        $mdRef->setAttribute('LOCTYPE', 'URL');
        $mdRef->setAttribute('LOCREF', trim($data->manifest));
        $mdRef->setAttribute('MIMETYPE', 'application/ld+json');
        $mdRef->setAttribute('LABEL', 'IIIF Presentation Manifest');
        $mdForManifest->appendChild($mdRef);
        $mdSec->appendChild($mdForManifest);

        return $mdSec;
    }

    private function buildMd(DOMDocument $dom, string $id, string $use): DOMElement
    {
        $md = $dom->createElement('mets:md');
        $md->setAttribute('ID', $id);
        $md->setAttribute('USE', $use);

        return $md;
    }

    private function buildMdWrap(
        DOMDocument $dom,
        string $id,
        string $use,
        ?string $mdType,
        callable $xmlDataBuilder,
    ): DOMElement {
        $md = $this->buildMd($dom, $id, $use);

        $mdWrap = $dom->createElement('mets:mdWrap');
        if ($mdType) {
            $mdWrap->setAttribute('MDTYPE', $mdType);
        }

        $xmlData = $dom->createElement('mets:xmlData');
        $xmlDataBuilder($xmlData);

        $mdWrap->appendChild($xmlData);
        $md->appendChild($mdWrap);

        return $md;
    }

    private function buildFileSec(DOMDocument $dom, MetsStoryData $data): ?DOMElement
    {
        $hasPreview = (bool) $data->previewImage;
        $hasItems   = count($data->items) > 0;

        if (!$hasPreview && !$hasItems) {
            return null;
        }

        $fileSec = $dom->createElement('mets:fileSec');

        // story preview image
        if ($hasPreview) {
            $fileGrp = $dom->createElement('mets:fileGrp');
            $fileGrp->setAttribute('USE', 'ACCESS');
            $fileGrp->appendChild($this->buildFileEntry(
                $dom,
                'STORY_IMG_001',
                null,
                'image/jpeg',
                $data->previewImage,
            ));
            $fileSec->appendChild($fileGrp);
        }

        // item images and ALTO transcriptions
        if ($hasItems) {
            $imageGroup = $dom->createElement('mets:fileGrp');
            $imageGroup->setAttribute('USE', 'ACCESS');

            $altoGrp = $dom->createElement('mets:fileGrp');
            $altoGrp->setAttribute('USE', 'ALTO');

            foreach ($data->items as $item) {
                $imageGroup->appendChild($this->buildFileEntry(
                    $dom,
                    'IMG_' . $item->itemId,
                    null,
                    'image/jpeg',
                    $item->imageLink,
                ));

                $altoGrp->appendChild($this->buildFileEntryInline(
                    $dom,
                    'ALTO_' . $item->itemId,
                    'application/xml',
                    $item->altoXml,
                ));
            }

            $fileSec->appendChild($imageGroup);
            $fileSec->appendChild($altoGrp);
        }

        return $fileSec;
    }

    private function buildFileEntryInline(
        DOMDocument $dom,
        string $id,
        string $mimeType,
        string $xmlString,
    ): DOMElement {
        $file = $dom->createElement('mets:file');
        $file->setAttribute('ID', $id);
        $file->setAttribute('MIMETYPE', $mimeType);

        $fContent = $dom->createElement('mets:FContent');
        $xmlData  = $dom->createElement('mets:xmlData');

        $altoDoc = new DOMDocument();
        $altoDoc->loadXML($xmlString);

        $imported = $dom->importNode($altoDoc->documentElement, true);
        $xmlData->appendChild($imported);

        $fContent->appendChild($xmlData);
        $file->appendChild($fContent);

        return $file;
    }

    private function buildFileEntry(
        DOMDocument $dom,
        string $id,
        ?string $use,
        string $mimeType,
        string $href,
    ): DOMElement {
        $file = $dom->createElement('mets:file');
        $file->setAttribute('ID', $id);
        $file->setAttribute('MIMETYPE', $mimeType);

        if ($use) {
            $file->setAttribute('USE', $use);
        }

        $fLocat = $dom->createElement('mets:FLocat');
        $fLocat->setAttribute('LOCTYPE', 'URL');
        $fLocat->setAttribute('LOCREF', $href);
        $file->appendChild($fLocat);

        return $file;
    }

    private function buildStructSec(DOMDocument $dom, MetsStoryData $data): DOMElement
    {
        $structSec = $dom->createElement('mets:structSec');
        $structSec->appendChild($this->buildStructMap($dom, $data));

        return $structSec;
    }

    private function buildStructMap(DOMDocument $dom, MetsStoryData $data): DOMElement
    {
        $structMap = $dom->createElement('mets:structMap');
        $structMap->setAttribute('TYPE', 'LOGICAL');

        $storyDiv = $dom->createElement('mets:div');
        $storyDiv->setAttribute('TYPE', 'story');
        $storyDiv->setAttribute('ID', 'STORY_' . $data->storyId);
        $storyDiv->setAttribute('LABEL', $data->dc['Title'] ?? '');
        $storyDiv->setAttribute('MDID', 'MD_DC MD_DCTERMS MD_EDM MD_PREMIS_EVENT MD_PREMIS_RIGHTS MD_IIIF_MANIFEST');

        if ($data->previewImage) {
            $fptr = $dom->createElement('mets:fptr');
            $fptr->setAttribute('FILEID', 'STORY_IMG_001');
            $storyDiv->appendChild($fptr);
        }

        foreach ($data->items as $item) {
            $itemDiv = $dom->createElement('mets:div');
            $itemDiv->setAttribute('TYPE', 'item');
            $itemDiv->setAttribute('ID', 'ITEM_' . $item->itemId);
            $itemDiv->setAttribute('ORDER', (string) $item->order);

            $imgFptr = $dom->createElement('mets:fptr');
            $imgFptr->setAttribute('FILEID', 'IMG_' . $item->itemId);
            $itemDiv->appendChild($imgFptr);

            $altoFptr = $dom->createElement('mets:fptr');
            $altoFptr->setAttribute('FILEID', 'ALTO_' . $item->itemId);
            $itemDiv->appendChild($altoFptr);

            $storyDiv->appendChild($itemDiv);
        }

        $structMap->appendChild($storyDiv);

        return $structMap;
    }

    private function toDateTime(string $timestamp): string
    {
        return str_replace(' ', 'T', $timestamp);
    }
}
