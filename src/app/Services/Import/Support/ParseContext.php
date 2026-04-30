<?php

namespace App\Services\Import\Support;

use App\Services\Import\DTO\ParsedJsonLdData;

final class ParseContext
{
    public array $fields = [];
    public string $manifestUrl = '';
    public string $pdfImage = '';
    public string $externalRecordId = '';
    public string $recordId = '';
    public string $manifestAuthMode = 'public';

    public function __construct(?string $iiifUrl = null)
    {
        if ($iiifUrl !== null && $iiifUrl !== '') {
            $this->manifestUrl = $iiifUrl;
            $this->manifestAuthMode = 'token';
        }
    }

    public function appendField(string $key, string $value): void
    {
        $this->fields[$key] = isset($this->fields[$key])
            ? $this->fields[$key] . ' || ' . $value
            : $value;
    }

    public function setManifestUrl(string $manifestUrl, string $authMode = 'public'): void
    {
        if ($this->manifestUrl !== '') {
            return;
        }

        $this->manifestUrl = $manifestUrl;
        $this->manifestAuthMode = $authMode;
    }

    public function toDto(): ParsedJsonLdData
    {
        return new ParsedJsonLdData(
            fields: $this->fields,
            manifestUrl: $this->manifestUrl,
            pdfImage: $this->pdfImage,
            externalRecordId: $this->externalRecordId,
            recordId: $this->recordId,
            manifestAuthMode: $this->manifestAuthMode,
        );
    }
}
