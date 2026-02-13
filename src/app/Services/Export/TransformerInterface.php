<?php

namespace App\Services\Export;

use App\Models\Story;
use Illuminate\Support\Collection;

interface TransformerInterface
{
    public function transformStory(Story $story, array $exclude = []): array;
    public function transformItems(Collection $itemIds, array $exclude = []): array;
}
