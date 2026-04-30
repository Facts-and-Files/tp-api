<?php

namespace App\Services\Import\Support;

use App\Services\Import\DTO\ParsedJsonLdData;

final class ParseContext
{
    public function __construct(
        public array $fields = [],
        public string $manifestUrl = '',
        public string $pdfImage = '',
        public string $externalRecordId = '',
        public string $recordId = '',
        public string $manifestAuthMode = 'public',
    ) {
    }

    public function appendField(string $field, string $value): void
    {
        if ($value === '') {
            return;
        }

        if (!isset($this->fields[$field]) || $this->fields[$field] === '') {
            $this->fields[$field] = $value;
            return;
        }

        $existingParts = array_filter(array_map('trim', explode(' || ', $this->fields[$field])));
        $newParts = array_filter(array_map('trim', explode(' || ', $value)));

        foreach ($newParts as $newPart) {
            if (!in_array($newPart, $existingParts, true)) {
                $existingParts[] = $newPart;
            }
        }

        $this->fields[$field] = implode(' || ', $existingParts);
    }

    public function setManifestUrl(string $url, string $authMode): void
    {
        if ($this->manifestUrl !== '') {
            return;
        }

        $this->manifestUrl = $url;
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
