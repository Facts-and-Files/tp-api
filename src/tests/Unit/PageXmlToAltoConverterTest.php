<?php

namespace Tests\Unit;

use App\Services\Converter\DTO\AltoPageData;
use App\Services\Converter\PageXmlToAltoConverter;
use App\Services\Page2AltoApiClient;
use DOMDocument;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PageXmlToAltoConverterTest extends TestCase
{
    private string $endpoint = 'https://page2alto.example.com/convert';

    private string $apiKey = 'test-api-key';

    private function makeConverter(): PageXmlToAltoConverter
    {
        return new PageXmlToAltoConverter(
            page2AltoApiClient: new Page2AltoApiClient(
                apiKey: $this->apiKey,
                endpoint: $this->endpoint,
            ),
        );
    }

    public function test_posts_page_xml_and_returns_alto_dom(): void
    {
        Http::fake([
            $this->endpoint => Http::response(
                <<<XML
                <alto xmlStringlns="http://www.loc.gov/standards/alto/ns-v4#">
                  <Layout>
                    <Page ID="p1" WIDTH="1000" HEIGHT="500" />
                  </Layout>
                </alto>
                XML,
                200,
                ['Content-Type' => 'application/xml'],
            ),
        ]);

        $converter = $this->makeConverter();

        $pageData = new AltoPageData(
            id: 1,
            fileIdentifier: 'XX33XX',
            fileName: 'https://iiif.example.com/XX33XX/full/full/0/default.jpg',
            order: 1,
            width: 1000,
            height: 500,
        );

        $pageXml = '<PcGts><Page/></PcGts>';

        $dom = $converter->convert($pageXml, $pageData);

        $this->assertInstanceOf(DOMDocument::class, $dom);
        $xmlString = $dom->saveXML();
        $this->assertIsString($xmlString);
        $this->assertStringContainsString('<alto', $xmlString);

        Http::assertSent(function ($request) use ($pageXml) {
            return $request->url() === $this->endpoint
                && $request->method() === 'POST'
                && $request->body() === $pageXml
                && $request->header('Accept')[0] === 'application/xml';
        });
    }

    public function test_overrides_page_metadata_with_local_values(): void
    {
        Http::fake([
            $this->endpoint => Http::response(
                // ALTO with remote metadata that should be overridden
                <<<XML
                <alto xmlns="http://www.loc.gov/standards/alto/ns-v4#">
                  <Description>
                    <MeasurementUnit>mm10</MeasurementUnit>
                    <sourceImageInformation>
                      <fileName>WRONG</fileName>
                      <fileIdentifier>WRONG</fileIdentifier>
                    </sourceImageInformation>
                  </Description>
                  <Layout>
                    <Page ID="wrong" WIDTH="1" HEIGHT="1">
                      <PrintSpace WIDTH="1" HEIGHT="1" HPOS="999" VPOS="999"/>
                    </Page>
                  </Layout>
                </alto>
                XML,
                200,
                ['Content-Type' => 'application/xml'],
            ),
        ]);

        $pageData = new AltoPageData(
            id: 42,
            fileIdentifier: 'XX33XX',
            fileName: 'https://iiif.example.com/XX33XX/full/full/0/default.jpg',
            order: 3,
            width: 1000,
            height: 500,
        );

        $converter = $this->makeConverter();
        $dom = $converter->convert('<PcGts><Page/></PcGts>', $pageData);

        $xml = simplexml_load_string($dom->saveXML());
        $xml->registerXPathNamespace('alto', 'http://www.loc.gov/standards/alto/ns-v4#');

        $this->assertSame(
            'pixel',
            (string) $xml->xpath('//alto:Description/alto:MeasurementUnit')[0],
        );
        $this->assertSame(
            $pageData->fileName,
            (string) $xml->xpath('//alto:Description/alto:sourceImageInformation/alto:fileName')[0],
        );
        $this->assertSame(
            (string) $pageData->id,
            (string) $xml->xpath('//alto:Layout/alto:Page/@ID')[0],
        );
    }

    public function test_throws_on_error_response(): void
    {
        Http::fake([
            $this->endpoint => Http::response('Error', 500),
        ]);

        $converter = $this->makeConverter();

        $pageData = new AltoPageData(
            id: 1,
            fileIdentifier: 'XX33XX',
            fileName: 'x',
            order: 1,
            width: 100,
            height: 200,
        );

        $this->expectException(RequestException::class);

        $converter->convert('<PcGts/>', $pageData);
    }

    public function test_throws_on_invalid_alto_xml(): void
    {
        Http::fake([
            $this->endpoint => Http::response('not-xml-at-all', 200),
        ]);

        $converter = $this->makeConverter();

        $pageData = new AltoPageData(
            id: 1,
            fileIdentifier: 'XX33XX',
            fileName: 'x',
            order: 1,
            width: 100,
            height: 200,
        );

        $this->expectException(\RuntimeException::class);

        $converter->convert('<PcGts/>', $pageData);
    }
}
