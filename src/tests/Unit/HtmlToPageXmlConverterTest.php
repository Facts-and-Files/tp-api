<?php

namespace Tests\Unit;

use App\Services\Converter\HtmlToPageXmlConverter;
use App\Services\Converter\DTO\PageXmlPageData;
use Tests\TestCase;

class HtmlToPageXmlConverterTest extends TestCase
{
    private HtmlToPageXmlConverter $converter;

    private PageXmlPageData $pageData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new HtmlToPageXmlConverter();
        $this->pageData = new PageXmlPageData(
            id: '1',
            fileIdentifier: 'XX33XX',
            fileName: 'https://iiif.example.com/XX33XX/full/full/0/default.jpg',
            order: 1,
            width: 1000,
            height: 500,
            plainText: 'Some text',
            htmlText: '<p>Some text</p>',
        );
    }

    public function test_is_a_valid_xml_string(): void
    {
        $dom = $this->converter->convert('<p>Some text</p>', $this->pageData);
        $result = $dom->saveXML();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_is_well_formed_xml(): void
    {
        $dom = $this->converter->convert('<p>Some text</p>', $this->pageData);
        $result = $dom->saveXML();
        $xml = simplexml_load_string($result);

        $this->assertNotFalse($xml, 'Result is not valid XML');
    }

    public function test_includes_the_page_root_element(): void
    {
        $dom = $this->converter->convert('<p>Some text</p>', $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('<PcGts', $result);
        $this->assertStringContainsString('<Page', $result);
    }

    public function test_creates_one_text_region_for_one_paragraph(): void
    {
        $dom = $this->converter->convert('<p>Block of text</p>', $this->pageData);
        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('page', 'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15');

        $regions = $xml->xpath('//page:TextRegion');

        $this->assertCount(1, $regions);
    }

    public function test_handles_multiple_paragraphs(): void
    {
        $dom = $this->converter->convert('<p>First paragraph</p><p>Second paragraph</p>', $this->pageData);
        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('page', 'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15');

        $regions = $xml->xpath('//page:TextRegion');

        $this->assertCount(2, $regions);
    }

    public function test_handles_plain_text_without_tags(): void
    {
        $dom = $this->converter->convert('No tags here', $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('No tags here', $result);
    }

    public function test_maps_headings_to_heading_regions(): void
    {
        $dom = $this->converter->convert('<h2>Invités</h2>', $this->pageData);
        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('page', 'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15');

        $regions = $xml->xpath('//page:TextRegion[@type="heading"]');

        $this->assertCount(1, $regions);
    }

    public function test_maps_list_items_to_text_lines(): void
    {
        $dom = $this->converter->convert('<ul><li>One</li><li>Two</li></ul>', $this->pageData);
        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('page', 'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15');

        $lines = $xml->xpath('//page:TextRegion[@type="list"]/page:TextLine');

        $this->assertCount(2, $lines);
    }

    public function test_maps_tables_to_table_regions(): void
    {
        $html = '<table><tr><th>Name</th><th>Role</th></tr><tr><td>Mellin</td><td>Vicaire</td></tr></table>';
        $dom = $this->converter->convert($html, $this->pageData);
        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('page', 'http://schema.primaresearch.org/PAGE/gts/pagecontent/2019-07-15');

        $regions = $xml->xpath('//page:TableRegion');

        $this->assertCount(1, $regions);
    }

    public function test_preserves_inline_annotation_metadata_in_custom(): void
    {
        $html = '<p>Herr <span style="text-decoration: underline;">Meyer</span></p>';
        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('underline', $result);
        $this->assertStringContainsString('<Custom>', $result);
    }

    public function test_adds_reading_order(): void
    {
        $dom = $this->converter->convert('<p>First</p><p>Second</p>', $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('<ReadingOrder>', $result);
        $this->assertStringContainsString('RegionRefIndexed', $result);
    }

    public function test_handles_missing_marker_images(): void
    {
        $html = '<p><img class="tct_missing" src="missing.gif" alt="missing"> text</p>';
        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('⟦missing⟧', $result);
        $this->assertStringContainsString('missing', $result);
    }

    public function test_produces_same_xml_for_same_input(): void
    {
        $html = '<p>Deterministic output</p>';

        $first = $this->converter->convert($html, $this->pageData)->saveXML();
        $second = $this->converter->convert($html, $this->pageData)->saveXML();

        $this->assertSame($first, $second);
    }
}
