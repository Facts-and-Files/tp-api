<?php

namespace App\EDM;

final class Literal
{
    public function __construct(
        public readonly string $value,
        public readonly ?string $language = null,
        public readonly ?string $datatype = null
    ) {}

    public function __toString(): string
    {
        return $this->value;
    }

    public function hasLanguage(): bool
    {
        return $this->language !== null;
    }

    public function hasDatatype(): bool
    {
        return $this->datatype !== null;
    }
}
