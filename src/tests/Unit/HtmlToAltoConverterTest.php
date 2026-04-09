<?php

namespace Tests\Unit;

use App\Services\Converter\DTO\AltoPageData;
use App\Services\Converter\HtmlToAltoConverter;
use Tests\TestCase;

class HtmlToAltoConverterTest extends TestCase
{
    private HtmlToAltoConverter $converter;

    private AltoPageData $pageData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->converter = new HtmlToAltoConverter();

        $this->pageData = new AltoPageData(
            id: 1,
            fileIdentifier: 'XX33XX',
            fileName: 'https://iiif.example.com/XX33XX/full/full/0/default.jpg',
            order: 1,
            width: 1000,
            height: 500,
        );
    }

    public function test_is_a_valid_xml_string()
    {
        $html = '<p>Some text</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_is_well_formed_xml()
    {
        $html = '<p>Some text</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $xml = simplexml_load_string($result);
        $this->assertNotFalse($xml, 'Result is not valid XML');
    }

    public function test_includes_the_alto_root_element()
    {
        $html = '<p>Some text</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('<alto xmlns', $result);
    }

    // -------------------------------------------------------------------------
    // ALTO structure
    // -------------------------------------------------------------------------

    public function test_description_has_correct_measurement_unit()
    {
        $html = '';

        $dom = $this->converter->convert($html, $this->pageData);
        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('alto', 'http://www.loc.gov/standards/alto/ns-v4#');

        $this->assertEquals(
            'pixel',
            (string) $xml->xpath('//alto:MeasurementUnit')[0],
        );
    }

    public function test_source_image_information_contains_iiif_data()
    {
        $html = '';

        $dom = $this->converter->convert($html, $this->pageData);
        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('alto', 'http://www.loc.gov/standards/alto/ns-v4#');

        $this->assertEquals(
            $this->pageData->fileName,
            (string) $xml->xpath('//alto:fileName')[0],
        );
        $this->assertEquals(
            $this->pageData->fileIdentifier,
            (string) $xml->xpath('//alto:fileIdentifier')[0],
        );
    }

    public function test_page_has_correct_dimensions()
    {
        $html = '';

        $dom = $this->converter->convert($html, $this->pageData);
        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('alto', 'http://www.loc.gov/standards/alto/ns-v4#');

        $page = $xml->xpath('//alto:Page')[0];
        $this->assertEquals($this->pageData->id, (string) $page['ID']);
        $this->assertEquals($this->pageData->order, (string) $page['PHYSICAL_IMG_NR']);
        $this->assertEquals($this->pageData->width, (string) $page['WIDTH']);
        $this->assertEquals($this->pageData->height, (string) $page['HEIGHT']);
    }

    public function test_print_space_has_correct_dimensions()
    {
        $html = '';

        $dom = $this->converter->convert($html, $this->pageData);
        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('alto', 'http://www.loc.gov/standards/alto/ns-v4#');

        $printSpace = $xml->xpath('//alto:PrintSpace')[0];
        $this->assertEquals($this->pageData->width, (string) $printSpace['WIDTH']);
        $this->assertEquals($this->pageData->height, (string) $printSpace['HEIGHT']);
        $this->assertEquals('0', (string) $printSpace['HPOS']);
        $this->assertEquals('0', (string) $printSpace['VPOS']);
    }

    public function test_wraps_text_in_a_text_block()
    {
        $html = '<p>Block of text</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('<TextBlock', $result);
    }

    public function test_maps_paragraph_to_a_text_line()
    {
        $html = '<p>Line of text</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('<TextLine', $result);
    }

    public function test_maps_words_to_string_elements()
    {
        $html = '<p>Word</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('<String', $result);
        $this->assertStringContainsString('Word', $result);
    }

    public function test_preserves_all_words_from_input()
    {
        $html = '<p>Hello beautiful world</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('Hello', $result);
        $this->assertStringContainsString('beautiful', $result);
        $this->assertStringContainsString('world', $result);
    }

    public function test_handles_multiple_paragraphs()
    {
        $html = '<p>First paragraph</p><p>Second paragraph</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $xml = simplexml_load_string($result);
        $xml->registerXPathNamespace('alto', 'http://www.loc.gov/standards/alto/ns-v4#');
        $blocks = $xml->xpath('//alto:TextBlock');

        $this->assertCount(2, $blocks);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function test_handles_plain_text_without_tags()
    {
        $html = 'No tags here';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('No', $result);
        $this->assertStringContainsString('tags', $result);
        $this->assertStringContainsString('here', $result);
    }

    public function test_strips_unsupported_html_tags()
    {
        $html = '<div><span style="color:red">Styled message</span></div>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringNotContainsString('<span', $result);
        $this->assertStringContainsString('Styled', $result);
        $this->assertStringContainsString('message', $result);
    }

    public function test_encodes_special_xml_characters()
    {
        $html = '<p>Cats &amp; Dogs</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $xml = simplexml_load_string($result);
        $this->assertNotFalse($xml);
    }

    public function test_handles_nested_html_elements()
    {
        $html = '<div><p>Nested <strong>text</strong> here</p></div>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('Nested', $result);
        $this->assertStringContainsString('text', $result);
        $this->assertStringContainsString('here', $result);
    }

    public function test_handles_unicode_characters()
    {
        $html = '<p>Ångström Ä Ö Ü</p>';

        $dom = $this->converter->convert($html, $this->pageData);
        $result = $dom->saveXML();

        $this->assertStringContainsString('Ångström', $result);
    }

    // -------------------------------------------------------------------------
    // Idempotency & determinism
    // -------------------------------------------------------------------------

    public function it_produces_same_output_for_same_input()
    {
        $html = '<p>Deterministic output</p>';

        $first = $this->converter->convert($html, $this->pageData);
        $second = $this->converter->convert($html, $this->pageData);

        $this->assertSame($first, $second);
    }
}
