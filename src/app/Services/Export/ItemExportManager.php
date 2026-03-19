<?php

namespace App\Services\Export;

use App\Models\Item;
use App\Services\Converter\HtmlToAltoConverter;
use App\Services\Converter\PageXmlToAltoConverter;
use App\Services\Page2AltoApiClient;
use Illuminate\Validation\ValidationException;

class ItemExportManager
{
    public function __construct(
        private AltoItemExporter $altoItemExporter,
        private HtmlToAltoConverter $htmlConverter,
    ) {}

    public function export(Item $item, string $format): string
    {
        return match ($format) {
            'alto' => $this->exportToAlto($item),
            default => throw ValidationException::withMessages(
                ["Export format {$format} is not supported."],
            ),
        };
    }

    protected function exportToAlto(Item $item): string
    {
        if ($item->TranscriptionSource === 'manual') {
            return $this->altoItemExporter->exportWithConverter($item, $this->htmlConverter);
        }

        $page2AltoApiClient = new Page2AltoApiClient(
            endpoint: config('apis.page2alto.api_url'),
            apiKey: config('apis.page2alto.api_token'),
        );

        $pageConverter = new PageXmlToAltoConverter(
            page2AltoApiClient: $page2AltoApiClient,
        );

        return $this->altoItemExporter->exportWithConverter($item, $pageConverter);
    }
}
