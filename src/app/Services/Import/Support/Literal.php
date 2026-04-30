<?php

namespace App\Services\Import\Support;

final readonly class Literal
{
    public function __construct(
        public string $value,
        public ?string $language = null,
    ) {
    }

    public function normalizedValue(): string
    {
        return mb_strtolower(trim($this->value));
    }

    public function identity(): string
    {
        return $this->normalizedValue() . '|' . mb_strtolower((string) $this->language);
    }
}
