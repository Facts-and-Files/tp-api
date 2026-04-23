<?php

declare(strict_types=1);

namespace App\Services\Import\DTO;

final class ParsedJsonLdData
{
    public function __construct(
        readonly public array $fields,
        readonly public string $manifestUrl,
        readonly public bool $manifestConverted,
        readonly public string $pdfImage,
        readonly public array $imageLinks,
        readonly public string $externalRecordId,
        readonly public string $recordId,
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
