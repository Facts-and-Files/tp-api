<?php

namespace App\Services\Import;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class IiifManifestClient
{
    public function __construct(
        private readonly DeiTokenClient    $tokenClient,
        private readonly IiifManifestParser $parser,
    ) {}

    public function fetch(
        string $manifestUrl,
        string $pdfImage = '',
        string $authMode = 'public',
    ): array {
        $raw = $authMode === 'token'
            ? $this->fetchWithToken($manifestUrl)
            : $this->fetchPublic($manifestUrl);

        return $this->parser->parse($raw, $pdfImage);
    }

    private function fetchPublic(string $url): array
    {
        $response = Http::withHeaders([
            'Accept'     => 'application/ld+json, application/json',
            'User-Agent' => 'Mozilla/5.0 (compatible; DEI-Importer/1.0)',
        ])->get($url);

        return $this->validateResponse($response, $url);
    }

    private function fetchWithToken(string $url): array
    {
        $token    = $this->tokenClient->getAccessToken();
        $response = Http::withToken($token)->get($url);

        return $this->validateResponse($response, $url);
    }

    private function validateResponse(Response $response, string $url): array
    {
        if ($response->failed()) {
            throw new RuntimeException(
                'IIIF manifest not reachable. Status: ' . $response->status()
            );
        }

        $manifest = $response->json();

        if (!is_array($manifest)) {
            throw new RuntimeException(
                'IIIF manifest response was not a JSON object/array for URL: ' . $url
            );
        }

        return $manifest;
    }
}
