<?php

namespace App\Services\Converter\DTO;

readonly class MetsStoryData
{
    public function __construct(
        public string $storyId,
        public array $dc,
        public array $dcterms,
        public array $edm,
        public string $manifest,
        public ?string $previewImage,
        public string $projectName,
        public string $recordId,
        public string $timestamp,
        public string $lastUpdated,
        public array $items,
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
