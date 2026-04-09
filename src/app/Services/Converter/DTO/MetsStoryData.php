<?php

namespace App\Services\Converter\DTO;

class MetsStoryData
{
    public function __construct(
        public readonly string $storyId,
        public readonly array $dc,
        public readonly array $dcterms,
        public readonly array $edm,
        public readonly string $manifest,
        public readonly ?string $previewImage,
        public readonly string $projectName,
        public readonly string $recordId,
        public readonly string $timestamp,
        public readonly string $lastUpdated,
        public readonly array $items,
    ) {}

    public function with(array $overrides): self
    {
        return new self(
            storyId: $overrides['storyId'] ?? $this->storyId,
            projectName: $overrides['projectName'] ?? $this->projectName,
            recordId: $overrides['recordId'] ?? $this->recordId,
            previewImage: array_key_exists('previewImage', $overrides)
                ? $overrides['previewImage']
                : $this->previewImage,
            manifest: $overrides['manifest'] ?? $this->manifest,
            timestamp: $overrides['timestamp'] ?? $this->timestamp,
            lastUpdated: $overrides['lastUpdated'] ?? $this->lastUpdated,
            dc: $overrides['dc'] ?? $this->dc,
            dcterms: $overrides['dcterms'] ?? $this->dcterms,
            edm: $overrides['edm'] ?? $this->edm,
            items: $overrides['items'] ?? $this->items,
        );
    }
}
