<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Page2AltoApiClient
{
    public function __construct(
        private readonly string $endpoint,
        private readonly ?string $apiKey = null,
    ) {}

    public function convert(string $pageXml): string
    {
        $response = Http::withToken($this->apiKey)
            ->attach('file', $pageXml, 'page.xml')
            ->post($this->endpoint);

        $response->throw();

        return $response->body();
    }
}
