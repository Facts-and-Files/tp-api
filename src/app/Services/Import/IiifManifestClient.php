<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class IiifManifestClient
{
    private string $ssoUrl;
    private string $clientSecret;
    private string $clientId;

    public function __construct()
    {
        $this->ssoUrl = config('services.europena_iiif.sso_url');
        $this->clientSecret = config('services.europena_iiif.client_secret');
        $this->clientId = config('services.europena_iiif.client_id', 'tp-api-client');
    }

    public function fetch(
        string $manifestUrl,
        string $pdfImage = '',
        string $authMode = 'public',
    ): array {
        $manifest = $authMode === 'token'
            ? $this->getManifestWithToken($manifestUrl)
            : $this->getManifestPublic($manifestUrl);

        $canvases = data_get($manifest, 'sequences.0.canvases', []);
        $imageLinks = $this->extractImageLinks($canvases, $pdfImage);

        return [
            'canvases' => $canvases,
            'imageLinks' => $imageLinks,
        ];
    }

    private function getManifestWithToken(string $url): array
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->get($url);

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

        return $response->json();
    }

    private function getManifestPublic(string $url): array
    {
        $response = Http::get($url);

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

        return $response->json();
    }

    private function getAccessToken(): string
    {
        $response = Http::asForm()->post($this->ssoUrl, [
            'grant_type' => 'client_credentials',
            'client_secret' => $this->clientSecret,
            'client_id' => $this->clientId,
        ])->throw();

        return $response->json('access_token');
    }

    private function extractImageLinks(array $canvases, string $pdfImage): array
    {
        if ($pdfImage !== '') {
            return array_map(
                fn(int $i) => $pdfImage . '?page=' . $i,
                range(0, count($canvases) - 1)
            );
        }

        return array_map(function (array $canvas) {
            return data_get($canvas, 'images.0.resource.@id', '');
        }, $canvases);
    }
}
