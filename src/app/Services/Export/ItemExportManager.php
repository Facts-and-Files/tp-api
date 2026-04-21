<?php

namespace App\Services\Export;

use App\Models\Item;
use App\Services\Converter\HtmlToAltoConverter;
use App\Services\Converter\PageXmlToAltoConverter;
use App\Services\Converter\HtmlToPageXmlConverter;
use App\Services\Export\PageXmlItemExporter;
use App\Services\Page2AltoApiClient;
use Illuminate\Validation\ValidationException;

class ItemExportManager
{
    public function __construct(
        private AltoItemExporter $altoItemExporter,
        private HtmlToAltoConverter $htmlToAltoConverter,
        private PageXmlItemExporter $pageXmlItemExporter,
        private HtmlToPageXmlConverter $htmlToPageXmlConverter,
    ) {}

    public function export(Item $item, string $format): string
    {
        return match ($format) {
            'alto' => $this->exportToAlto($item),
            'pagexml' => $this->exportToPageXml($item),
            default => throw ValidationException::withMessages(
                ["Export format {$format} is not supported."],
            ),
        };
    }

    public function getCachedAlto(Item $item): ?string
    {
        return $this->altoItemExporter->getCached($item);
    }

    public function hasCachedAlto(Item $item): bool
    {
        return $this->altoItemExporter->hasCached($item);
    }

    public function getCachedPageXml(Item $item): ?string
    {
        return $this->pageXmlItemExporter->getCached($item);
    }

    public function hasCachedPageXml(Item $item): bool
    {
        return $this->pageXmlItemExporter->hasCached($item);
    }

    protected function exportToPageXml(Item $item): string
    {
        if ($item->TranscriptionSource === 'manual') {
            return $this->pageXmlItemExporter->exportWithConverter(
                $item,
                $this->htmlToPageXmlConverter,
            );
        }

        return $item->Transcription['Text'] ?? '';
    }

    protected function exportToAlto(Item $item): string
    {
        if ($item->TranscriptionSource === 'manual') {
            return $this->altoItemExporter->exportWithConverter(
                $item,
                $this->htmlToAltoConverter,
            );
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
