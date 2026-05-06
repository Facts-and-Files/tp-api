<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DeiTokenClient
{
    private string $ssoUrl;
    private string $clientSecret;
    private string $clientId;

    public function __construct()
    {
        $this->ssoUrl = config('services.europena_iiif.sso_url');
        $this->clientSecret = config('services.europena_iiif.client_secret');
        $this->clientId = config('services.europena_iiif.client_id');
    }

    public function getAccessToken(): string
    {
        $response = Http::asForm()->post($this->ssoUrl, [
            'grant_type'    => 'client_credentials',
            'client_secret' => $this->clientSecret,
            'client_id'     => $this->clientId,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'IIIF access token request failed. Status: ' . $response->status()
            );
        }

        $token = $response->json('access_token');

        if (!is_string($token) || $token === '') {
            throw new RuntimeException('IIIF access token response did not contain an access token.');
        }

        return $token;
    }
}
