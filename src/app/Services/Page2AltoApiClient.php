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
        $response = Http::withHeaders([
            'Accept' => 'application/xml',
            'Content-Type' => 'application/xml',
            'Authorization' => "Bearer {$this->apiKey}",
        ])
        ->withBody($pageXml, 'application/xml')
        ->post($this->endpoint);

        $response->throw();

        return $response->body();
    }
}
