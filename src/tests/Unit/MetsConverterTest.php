<?php

namespace Tests\Unit;

use App\Services\Converter\MetsConverter;
use App\Services\Converter\DTO\MetsItemData;
use App\Services\Converter\DTO\MetsStoryData;
use DateTimeImmutable;
use DateTimeZone;
use DOMDocument;
use DOMXPath;
use Tests\TestCase;

class MetsConverterTest extends TestCase
{
    private MetsConverter $converter;
    private MetsStoryData $storyData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new MetsConverter();
        $this->storyData = $this->storyDataFixture();
    }

    public function test_convert_returns_valid_xml_string(): void
    {
        $xml = $this->converter->convert($this->storyData)->saveXML();

        $this->assertIsString($xml);
        $this->assertStringStartsWith('<?xml', $xml);
    }

    public function test_convert_produces_parseable_dom(): void
    {
        $xml = $this->converter->convert($this->storyData)->saveXML();
        $dom = new DOMDocument();

        $this->assertTrue($dom->loadXML($xml));
    }

    public function test_root_element_has_correct_objid(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('2', $xpath->evaluate('string(/mets:mets/@OBJID)'));
    }

    public function test_root_element_has_mets_namespace(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(1, $xpath->query('/mets:mets')->length);
    }

    public function test_mets_hdr_has_create_date(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());
        $createDate = $xpath->evaluate('string(/mets:mets/mets:metsHdr/@CREATEDATE)');
        $dt = new DateTimeImmutable($createDate);
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $this->assertLessThanOrEqual(5, abs($now->getTimestamp() - $dt->getTimestamp()));
    }

    public function test_mets_hdr_agent_has_correct_role_and_type(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('ORGANIZATION', $xpath->evaluate('string(/mets:mets/mets:metsHdr/mets:agent/@TYPE)'));
        $this->assertSame('CREATOR', $xpath->evaluate('string(/mets:mets/mets:metsHdr/mets:agent/@ROLE)'));
    }

    public function test_mets_hdr_has_agent_name(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('Transcribathon Portal', $xpath->evaluate('string(/mets:mets/mets:metsHdr/mets:agent/mets:name)'));
    }

    public function test_mets_hdr_has_altrecordid_value(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('/135/_nnVvTts', $xpath->evaluate('string(/mets:mets/mets:metsHdr/mets:altRecordID)'));
    }

    public function test_mets_hdr_has_altrecordid_type_with_project_name(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'External Record ID from Project-1',
            $xpath->evaluate('string(/mets:mets/mets:metsHdr/mets:altRecordID/@TYPE)'),
        );
    }

    public function test_md_sec_contains_dc_mdwrap(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertTrue($xpath->evaluate('boolean(/mets:mets/mets:mdSec/mets:md/mets:mdWrap[@MDTYPE="DC"])'));
    }

    public function test_md_sec_dc_has_use_descriptive(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'DESCRIPTIVE',
            $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md[@ID="MD_DC"]/@USE)'),
        );
    }

    public function test_md_sec_contains_dc_title(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'Neues Deutschland - Ausgaben zwischen dem 27.10. und 30.11.1989',
            $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdWrap/mets:xmlData/dc:title)'),
        );
    }

    public function test_md_sec_contains_dc_creator(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('Jack Bates', $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdWrap/mets:xmlData/dc:creator)'));
    }

    public function test_md_sec_contains_dc_language(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('Latin', $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdWrap/mets:xmlData/dc:language)'));
    }

    public function test_md_sec_contains_dcterms_mdwrap(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertTrue($xpath->evaluate('boolean(/mets:mets/mets:mdSec/mets:md/mets:mdWrap[@MDTYPE="DCTERMS"])'));
    }

    public function test_md_sec_contains_dcterms_medium(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('Parchment', $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdWrap/mets:xmlData/dcterms:medium)'));
    }

    public function test_md_sec_contains_dcterms_provenance(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('CHA01', $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdWrap/mets:xmlData/dcterms:provenance)'));
    }

    public function test_md_sec_contains_edm_mdwrap(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertTrue($xpath->evaluate('boolean(/mets:mets/mets:mdSec/mets:md/mets:mdWrap[@MDTYPE="EDM"])'));
    }

    public function test_md_sec_contains_edm_landing_page(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'https://www.europeana.eu/portal/record/135/_nnVvTts.html',
            $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdWrap/mets:xmlData/edm:landingPage)'),
        );
    }

    public function test_md_sec_contains_edm_data_provider(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'Universitätsbibliothek Heidelberg',
            $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdWrap/mets:xmlData/edm:dataProvider)'),
        );
    }

    public function test_md_sec_contains_edm_rights(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'http://creativecommons.org/licenses/by-sa/3.0/de',
            $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdWrap/mets:xmlData/edm:rights)'),
        );
    }

    public function test_md_sec_contains_edm_country(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('Germany', $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdWrap/mets:xmlData/edm:country)'));
    }

    public function test_md_sec_skips_empty_edm_fields(): void
    {
        $data  = $this->storyDataFixture()->with(['edm' => array_merge($this->storyDataFixture()->edm, ['Country' => ''])]);
        $xpath = $this->buildXPath($this->converter->convert($data)->saveXML());

        $this->assertSame('', $xpath->evaluate('string(//mets:xmlData/edm:country)'));
    }

    public function test_md_sec_contains_premis_event_mdwrap(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertTrue($xpath->evaluate('boolean(/mets:mets/mets:mdSec/mets:md/mets:mdWrap[@MDTYPE="PREMIS:EVENT"])'));
    }

    public function test_md_sec_contains_premis_capture_event_with_date(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertTrue($xpath->evaluate('boolean(//premis:event[premis:eventType="capture"])'));
        $this->assertSame(
            '2022-02-23T09:57:03',
            $xpath->evaluate('string(//premis:event[premis:eventType="capture"]/premis:eventDateTime)'),
        );
    }

    public function test_md_sec_contains_premis_metadata_modification_event_with_date(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertTrue($xpath->evaluate('boolean(//premis:event[premis:eventType="metadata modification"])'));
        $this->assertSame(
            '2022-02-23T09:57:03',
            $xpath->evaluate('string(//premis:event[premis:eventType="metadata modification"]/premis:eventDateTime)'),
        );
    }

    public function test_md_sec_contains_premis_rights_mdwrap(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertTrue($xpath->evaluate('boolean(/mets:mets/mets:mdSec/mets:md/mets:mdWrap[@MDTYPE="PREMIS:RIGHTS"])'));
    }

    public function test_md_sec_contains_premis_rights_basis_license(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('license', $xpath->evaluate('string(//premis:rightsBasis)'));
    }

    public function test_md_sec_contains_premis_rights_identifier_type_local(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('local', $xpath->evaluate('string(//premis:rightsStatementIdentifierType)'));
    }

    public function test_md_sec_contains_premis_rights_license_identifier_value(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'http://creativecommons.org/licenses/by-sa/3.0/de',
            $xpath->evaluate('string(//premis:licenseDocumentationIdentifierValue)'),
        );
    }

    public function test_md_sec_contains_iiif_manifest_md_element(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(1, $xpath->query('//mets:md[@ID="MD_IIIF_MANIFEST"]')->length);
    }

    public function test_md_sec_iiif_manifest_has_locref(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'https://iiif.transcribathon.eu/iiif/manifest/bb7fe5cc-0b04-4de7-87c0-6e4051b23470.json',
            $xpath->evaluate('string(//mets:md[@ID="MD_IIIF_MANIFEST"]/mets:mdRef/@LOCREF)'),
        );
    }

    public function test_md_sec_iiif_manifest_has_loctype_url(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('URL', $xpath->evaluate('string(//mets:md[@ID="MD_IIIF_MANIFEST"]/mets:mdRef/@LOCTYPE)'));
    }

    public function test_md_sec_iiif_manifest_has_mimetype(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'application/ld+json',
            $xpath->evaluate('string(//mets:md[@ID="MD_IIIF_MANIFEST"]/mets:mdRef/@MIMETYPE)'),
        );
    }

    public function test_file_sec_is_absent_when_no_preview_and_no_items(): void
    {
        $data  = $this->storyDataFixture()->with(['previewImage' => null, 'items' => []]);
        $xpath = $this->buildXPath($this->converter->convert($data)->saveXML());

        $this->assertSame(0, $xpath->query('//mets:fileSec')->length);
    }

    public function test_file_sec_contains_preview_image_file(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $locref = $xpath->evaluate('string(//mets:file[@ID="STORY_IMG_001"]/mets:FLocat/@LOCREF)');

        $this->assertStringContainsString('ND-0001.tif/full/full/0/default.jpg', $locref);
    }

    public function test_file_sec_preview_image_fileGrp_has_use_access(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $use = $xpath->evaluate('string(//mets:file[@ID="STORY_IMG_001"]/parent::mets:fileGrp/@USE)');

        $this->assertSame('ACCESS', $use);
    }

    public function test_file_sec_contains_item_image_file(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $locref = $xpath->evaluate('string(//mets:file[@ID="IMG_253"]/mets:FLocat/@LOCREF)');

        $this->assertSame('https://example.org/iiif/page1/full/full/0/default.jpg', $locref);
    }

    public function test_file_sec_item_images_fileGrp_has_use_access(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $use = $xpath->evaluate('string(//mets:file[@ID="IMG_253"]/parent::mets:fileGrp/@USE)');

        $this->assertSame('ACCESS', $use);
    }

    public function test_file_sec_contains_inline_alto_xml(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertTrue(
            $xpath->evaluate('boolean(//mets:file[@ID="ALTO_253"]/mets:FContent/mets:xmlData/alto:alto)'),
        );
    }

    public function test_file_sec_alto_fileGrp_has_use_alto(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $use = $xpath->evaluate('string(//mets:file[@ID="ALTO_253"]/parent::mets:fileGrp/@USE)');

        $this->assertSame('ALTO', $use);
    }

    public function test_file_sec_item_image_has_jpeg_mimetype(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('image/jpeg', $xpath->evaluate('string(//mets:file[@ID="IMG_253"]/@MIMETYPE)'));
    }

    public function test_file_sec_alto_has_xml_mimetype(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('application/xml', $xpath->evaluate('string(//mets:file[@ID="ALTO_253"]/@MIMETYPE)'));
    }

    public function test_struct_map_is_present_with_logical_type(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(1, $xpath->query('//mets:structMap[@TYPE="LOGICAL"]')->length);
    }

    public function test_struct_map_story_div_has_correct_id_and_type(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('story', $xpath->evaluate('string(//mets:structMap/mets:div/@TYPE)'));
        $this->assertSame('STORY_2', $xpath->evaluate('string(//mets:structMap/mets:div/@ID)'));
    }

    public function test_struct_map_story_div_label_uses_dc_title(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'Neues Deutschland - Ausgaben zwischen dem 27.10. und 30.11.1989',
            $xpath->evaluate('string(//mets:structMap/mets:div/@LABEL)'),
        );
    }

    public function test_struct_map_story_div_has_mdid_references(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());
        $mdid  = $xpath->evaluate('string(//mets:structMap/mets:div/@MDID)');

        $this->assertStringContainsString('MD_DC', $mdid);
        $this->assertStringContainsString('MD_IIIF_MANIFEST', $mdid);
    }

    public function test_struct_map_story_div_has_preview_fptr(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame(
            'STORY_IMG_001',
            $xpath->evaluate('string(//mets:structMap/mets:div/mets:fptr/@FILEID)'),
        );
    }

    public function test_struct_map_item_div_ids_match_fixture(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('ITEM_253', $xpath->evaluate('string(//mets:structMap/mets:div/mets:div[1]/@ID)'));
        $this->assertSame('ITEM_254', $xpath->evaluate('string(//mets:structMap/mets:div/mets:div[2]/@ID)'));
    }

    public function test_struct_map_item_divs_have_correct_order(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('1', $xpath->evaluate('string(//mets:structMap/mets:div/mets:div[1]/@ORDER)'));
        $this->assertSame('2', $xpath->evaluate('string(//mets:structMap/mets:div/mets:div[2]/@ORDER)'));
    }

    public function test_struct_map_item_div_has_image_and_alto_fptr(): void
    {
        $xpath = $this->buildXPath($this->converter->convert($this->storyData)->saveXML());

        $this->assertSame('IMG_253', $xpath->evaluate('string(//mets:structMap/mets:div/mets:div[1]/mets:fptr[1]/@FILEID)'));
        $this->assertSame('ALTO_253', $xpath->evaluate('string(//mets:structMap/mets:div/mets:div[1]/mets:fptr[2]/@FILEID)'));
    }

    public function test_struct_map_fptr_fileids_match_fileSec_ids(): void
    {
        $dom = $this->converter->convert($this->storyData);
        $xml = $dom->saveXML();

        // IDs declared in fileSec
        $this->assertStringContainsString('ID="IMG_253"', $xml);
        $this->assertStringContainsString('ID="ALTO_253"', $xml);

        // Same IDs referenced in structMap fptr
        $this->assertStringContainsString('FILEID="IMG_253"', $xml);
        $this->assertStringContainsString('FILEID="ALTO_253"', $xml);
    }

    public function test_struct_map_no_item_divs_when_items_empty(): void
    {
        $data  = $this->storyDataFixture()->with(['items' => []]);
        $xpath = $this->buildXPath($this->converter->convert($data)->saveXML());

        $this->assertSame(0, $xpath->query('//mets:structMap/mets:div/mets:div[@TYPE="item"]')->length);
    }

    private function buildXPath(string $xml): DOMXPath
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('mets', 'http://www.loc.gov/METS/v2');
        $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $xpath->registerNamespace('dcterms', 'http://purl.org/dc/terms/');
        $xpath->registerNamespace('edm', 'http://www.europeana.eu/schemas/edm/');
        $xpath->registerNamespace('premis', 'http://www.loc.gov/premis/v3');
        $xpath->registerNamespace('alto', 'http://www.loc.gov/standards/alto/ns-v4#');

        return $xpath;
    }

    private function storyDataFixture(): MetsStoryData
    {
        return new MetsStoryData(
            storyId: 2,
            projectName: 'Project-1',
            recordId: '/135/_nnVvTts',
            previewImage: 'https://rhus-148.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//135/_nnVvTts/ND-0001.tif/full/full/0/default.jpg',
            manifest: 'https://iiif.transcribathon.eu/iiif/manifest/bb7fe5cc-0b04-4de7-87c0-6e4051b23470.json',
            timestamp: '2022-02-23 09:57:03',
            lastUpdated: '2022-02-23 09:57:03',
            dc: [
                'Title'       => 'Neues Deutschland - Ausgaben zwischen dem 27.10. und 30.11.1989',
                'Description' => 'Europeana 1989 - Berlin, 12-13.09.2014',
                'Creator'     => 'Jack Bates',
                'Source'      => 'Universitätsbibliothek Heidelberg',
                'Rights'      => 'Public Domain',
                'Language'    => 'Latin',
            ],
            dcterms: [
                'Medium'     => 'Parchment',
                'Created'    => '2014-11-10T12:05:42.172Z',
                'Provenance' => 'CHA01',
            ],
            edm: [
                'LandingPage'  => 'https://www.europeana.eu/portal/record/135/_nnVvTts.html',
                'Country'      => 'Germany',
                'DataProvider' => 'Universitätsbibliothek Heidelberg',
                'Provider'     => 'Universitätsbibliothek Heidelberg',
                'Rights'       => 'http://creativecommons.org/licenses/by-sa/3.0/de',
                'Year'         => '1916',
                'DatasetName'  => '07931_L_DE_UniLibHeidelberg_druckschriften_IIIF',
                'Begin'        => 'Thu Jan 01 00:19:32 CET 1903',
                'End'          => 'Thu Dec 31 00:19:32 CET 1903',
                'IsShownAt'    => 'http://digi.ub.uni-heidelberg.de/diglit/matrikelregister2',
                'Language'     => 'de',
                'Agent'        => 'Zvonimir Stipanović',
            ],
            items: [
                $this->itemDataFixture('253', 1),
                $this->itemDataFixture('254', 2),
            ],
        );
    }

    private function itemDataFixture(string $id = '253', int $order = 1): MetsItemData
    {
        return new MetsItemData(
            itemId: $id,
            order: $order,
            imageLink: 'https://example.org/iiif/page1/full/full/0/default.jpg',
            altoXml: '<?xml version="1.0"?><alto:alto xmlns:alto="http://www.loc.gov/standards/alto/ns-v4#"/>',
        );
    }
}
