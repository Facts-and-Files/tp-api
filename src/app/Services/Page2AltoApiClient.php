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
            ->accept('application/xml')
            ->attach(
                name: 'file',
                contents: $pageXml,
                filename: 'page.xml',
            )
            ->post($this->endpoint);

        $response->throw();

        return $response->body();
    }
}
