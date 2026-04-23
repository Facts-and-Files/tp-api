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

    public function fetch(string $manifestUrl, bool $converted, string $pdfImage = ''): array
    {
        $accessToken = $this->getAccessToken();
        $manifest = $this->getManifest($manifestUrl, $accessToken, $converted);

        $canvases = data_get($manifest, 'sequences.0.canvases', []);
        $imageLinks = $this->extractImageLinks($canvases, $pdfImage);

        return [
            'canvases' => $canvases,
            'imageLinks' => $imageLinks,
        ];
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

    private function getManifest(string $url, string $token, bool $converted): array
    {
        $response = Http::withToken($token)
            ->withoutRedirecting()
            ->get($url);

        // follow a single redirect when not yet converted
        if (!$converted && $response->redirect()) {
            $location = $response->header('Location') ?? $url;
            $response = Http::withToken($token)->get($location);
        }

        if ($response->failed()) {
            throw new RuntimeException(
                'IIIF manifest not reachable. Status: ' . $response->status()
            );
        }

        return $response->json();
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
