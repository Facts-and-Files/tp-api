<?php

namespace App\Services\Export;

use App\Models\Item;
use App\Services\Converter\HtmlToAltoConverter;
use App\Services\Converter\PageXmlToAltoConverter;
use Illuminate\Validation\ValidationException;

class ItemExportManager
{
    public function __construct(
        private AltoItemExporter $altoItemExporter,
        private HtmlToAltoConverter $htmlConverter,
    ) {
    }

    public function export(Item $item, string $format): string
    {
        return match ($format) {
            'alto' => $this->exportToAlto($item),
            default => throw ValidationException::withMessages(
                ["Export format {$format} is not supported."]
            ),
        };
    }

    protected function exportToAlto(Item $item): string
    {
        if ($item->TranscriptionSource === 'manual') {
            return $this->altoItemExporter->exportWithConverter($item, $this->htmlConverter);
        }

        $pageConverter = new PageXmlToAltoConverter(
            endpoint: config('apis.page2alto.api_url'),
            apiKey: config('apis.page2alto.api_token'),
        );

        return $this->altoItemExporter->exportWithConverter($item, $pageConverter);
    }
}
