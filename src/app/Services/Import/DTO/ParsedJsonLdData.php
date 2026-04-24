<?php

namespace App\Services\Import\DTO;

final readonly class ParsedJsonLdData
{
    public function __construct(
        public array $fields,
        public string $manifestUrl,
        public bool $manifestConverted,
        public string $pdfImage,
        public array $imageLinks,
        public string $externalRecordId,
        public string $recordId,
    ) {
    }

    public function hasRecordId(): bool
    {
        return $this->recordId !== '';
    }

    public function storyTitle(): string
    {
        return trim((string) ($this->fields['dc:title'] ?? ''));
    }
}
