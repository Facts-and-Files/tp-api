<?php

namespace App\Services\Converter;

use App\Models\Story;
use Illuminate\Support\Collection;

interface ConverterInterface
{
    public function convertStory(Story $story, array $exclude = []): array;
    public function convertItems(Collection $itemIds, array $exclude = []): array;
}
