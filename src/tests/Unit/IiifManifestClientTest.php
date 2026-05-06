<?php

namespace Tests\Unit;

use App\Services\Import\DeiTokenClient;
use App\Services\Import\IiifManifestClient;
use App\Services\Import\IiifManifestParser;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class IiifManifestClientTest extends TestCase
{
    public function test_parser_v2_extracts_canvases_and_image_links(): void
    {
        $result = (new IiifManifestParser())->parse($this->v2Manifest());

        $this->assertCount(2, $result['canvases']);
        $this->assertSame([
            'https://example.com/p1.jpg',
            'https://example.com/p2.jpg',
        ], $result['imageLinks']);
    }

    public function test_parser_returns_empty_canvases_and_links_when_sequences_missing(): void
    {
        $result = (new IiifManifestParser())->parse([]);

        $this->assertSame([], $result['canvases']);
        $this->assertSame([], $result['imageLinks']);
    }

    public function test_parser_uses_pdf_image_with_page_query_when_provided(): void
    {
        $result = (new IiifManifestParser())->parse($this->v2Manifest(), 'https://example.com/doc.pdf');

        $this->assertSame([
            'https://example.com/doc.pdf?page=0',
            'https://example.com/doc.pdf?page=1',
        ], $result['imageLinks']);
    }

    public function test_parser_v2_falls_back_to_empty_string_for_missing_image_id(): void
    {
        $manifest = [
            'sequences' => [[
                'canvases' => [
                    ['images' => [['resource' => []]]],
                ],
            ]],
        ];

        $result = (new IiifManifestParser())->parse($manifest);

        $this->assertSame([''], $result['imageLinks']);
    }

    public function test_parser_v3_extracts_canvases_and_image_links(): void
    {
        $result = (new IiifManifestParser())->parse($this->v3Manifest());

        $this->assertCount(2, $result['canvases']);
        $this->assertSame([
            'https://example.com/p1.jpg',
            'https://example.com/p2.jpg',
        ], $result['imageLinks']);
    }

    public function test_parser_v3_context_as_array_is_detected_correctly(): void
    {
        $manifest                = $this->v3Manifest();
        $manifest['@context']    = [
            'http://www.w3.org/ns/anno.jsonld',
            'http://iiif.io/api/presentation/3/context.json',
        ];

        $result = (new IiifManifestParser())->parse($manifest);

        $this->assertCount(2, $result['canvases']);
        $this->assertSame([
            'https://example.com/p1.jpg',
            'https://example.com/p2.jpg',
        ], $result['imageLinks']);
    }

    public function test_parser_v3_resolves_specific_resource_body(): void
    {
        $manifest = [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'items'    => [[
                'id'    => 'https://example.com/canvas/1',
                'type'  => 'Canvas',
                'items' => [[
                    'items' => [[
                        'body' => [
                            'type'   => 'SpecificResource',
                            'source' => ['id' => 'https://example.com/image.jpg'],
                        ],
                    ]],
                ]],
            ]],
        ];

        $result = (new IiifManifestParser())->parse($manifest);

        $this->assertSame(['https://example.com/image.jpg'], $result['imageLinks']);
    }

    public function test_parser_v3_returns_empty_when_items_missing(): void
    {
        $result = (new IiifManifestParser())->parse([
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
        ]);

        $this->assertSame([], $result['canvases']);
        $this->assertSame([], $result['imageLinks']);
    }

    public function test_parser_v3_falls_back_to_empty_string_for_missing_image_id(): void
    {
        $manifest = [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'items'    => [[
                'items' => [[
                    'items' => [[
                        'body' => ['type' => 'Image'],  // no 'id' key
                    ]],
                ]],
            ]],
        ];

        $result = (new IiifManifestParser())->parse($manifest);

        $this->assertSame([''], $result['imageLinks']);
    }

    public function test_parser_v3_uses_pdf_image_with_page_query_when_provided(): void
    {
        $result = (new IiifManifestParser())->parse($this->v3Manifest(), 'https://example.com/doc.pdf');

        $this->assertSame([
            'https://example.com/doc.pdf?page=0',
            'https://example.com/doc.pdf?page=1',
        ], $result['imageLinks']);
    }

    public function test_token_client_returns_access_token_on_success(): void
    {
        config()->set('services.europena_iiif.sso_url', 'https://sso.example.org/token');
        config()->set('services.europena_iiif.client_secret', 'secret');
        config()->set('services.europena_iiif.client_id', 'tp-api-client');

        Http::fake([
            'https://sso.example.org/token' => Http::response(['access_token' => 'test-token-abc'], 200),
        ]);

        $this->assertSame('test-token-abc', (new DeiTokenClient())->getAccessToken());
    }

    public function test_token_client_throws_when_sso_returns_error_status(): void
    {
        config()->set('services.europena_iiif.sso_url', 'https://sso.example.org/token');

        Http::fake([
            'https://sso.example.org/token' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IIIF access token request failed. Status: 401');

        (new DeiTokenClient())->getAccessToken();
    }

    public function test_token_client_throws_when_access_token_is_missing_from_response(): void
    {
        config()->set('services.europena_iiif.sso_url', 'https://sso.example.org/token');

        Http::fake([
            'https://sso.example.org/token' => Http::response(['other_key' => 'value'], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IIIF access token response did not contain an access token.');

        (new DeiTokenClient())->getAccessToken();
    }

    public function test_fetch_public_returns_parsed_manifest(): void
    {
        Http::fake([
            'https://example.com/manifest' => Http::response($this->v2Manifest(), 200),
        ]);

        $result = $this->makeClient()->fetch('https://example.com/manifest');

        $this->assertCount(2, $result['canvases']);
        $this->assertSame([
            'https://example.com/p1.jpg',
            'https://example.com/p2.jpg',
        ], $result['imageLinks']);
    }

    public function test_fetch_public_returns_parsed_v3_manifest(): void
    {
        Http::fake([
            'https://example.com/manifest' => Http::response($this->v3Manifest(), 200),
        ]);

        $result = $this->makeClient()->fetch('https://example.com/manifest');

        $this->assertCount(2, $result['canvases']);
        $this->assertSame([
            'https://example.com/p1.jpg',
            'https://example.com/p2.jpg',
        ], $result['imageLinks']);
    }

    public function test_fetch_throws_when_public_manifest_is_unreachable(): void
    {
        Http::fake([
            'https://example.com/manifest' => Http::response([], 404),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IIIF manifest not reachable. Status: 404');

        $this->makeClient()->fetch('https://example.com/manifest');
    }

    public function test_fetch_throws_when_response_is_not_json_array(): void
    {
        Http::fake([
            'https://example.com/manifest' => Http::response('not-json-array', 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IIIF manifest response was not a JSON object/array');

        $this->makeClient()->fetch('https://example.com/manifest');
    }

    public function test_fetch_with_token_uses_bearer_token_from_token_client(): void
    {
        config()->set('services.europena_iiif.sso_url', 'https://sso.example.org/token');

        Http::fake([
            'https://sso.example.org/token'          => Http::response(['access_token' => 'bearer-xyz'], 200),
            'https://example.com/protected/manifest' => Http::response($this->v2Manifest(), 200),
        ]);

        $result = $this->makeClient()->fetch('https://example.com/protected/manifest', '', 'token');

        $this->assertCount(2, $result['canvases']);

        Http::assertSent(fn($req) => $req->hasHeader('Authorization', 'Bearer bearer-xyz'));
    }

    public function test_fetch_with_token_throws_when_token_request_fails(): void
    {
        config()->set('services.europena_iiif.sso_url', 'https://sso.example.org/token');

        Http::fake([
            'https://sso.example.org/token' => Http::response([], 401),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IIIF access token request failed. Status: 401');

        $this->makeClient()->fetch('https://example.com/protected/manifest', '', 'token');
    }

    private function makeClient(): IiifManifestClient
    {
        return new IiifManifestClient(new DeiTokenClient(), new IiifManifestParser());
    }

    private function v2Manifest(): array
    {
        return [
            '@context'  => 'http://iiif.io/api/presentation/2/context.json',
            'sequences' => [[
                'canvases' => [
                    ['images' => [['resource' => ['@id' => 'https://example.com/p1.jpg']]]],
                    ['images' => [['resource' => ['@id' => 'https://example.com/p2.jpg']]]],
                ],
            ]],
        ];
    }

    private function v3Manifest(): array
    {
        return [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'items'    => [
                [
                    'id'    => 'https://example.com/canvas/1',
                    'type'  => 'Canvas',
                    'items' => [[
                        'items' => [[
                            'body' => ['type' => 'Image', 'id' => 'https://example.com/p1.jpg'],
                        ]],
                    ]],
                ],
                [
                    'id'    => 'https://example.com/canvas/2',
                    'type'  => 'Canvas',
                    'items' => [[
                        'items' => [[
                            'body' => ['type' => 'Image', 'id' => 'https://example.com/p2.jpg'],
                        ]],
                    ]],
                ],
            ],
        ];
    }
}
