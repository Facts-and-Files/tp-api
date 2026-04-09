<?php

namespace App\Services\Export;

use App\Models\Item;
use App\Models\Story;
use App\Services\Export\ItemExportManager;
use Illuminate\Support\Collection;

final class MetsStoryReadiness
{
    public function __construct(
        private readonly ItemExportManager $itemExportManager,
    ) {}

    public function missingItems(Story $story): Collection
    {
        return $this->storyItems($story)
            ->filter(fn (Item $item) => !$this->itemExportManager->hasCachedAlto($item))
            ->values();
    }

    public function isReady(Story $story): bool
    {
        return $this->missingItems($story)->isEmpty();
    }

    public function allItems(Story $story): Collection
    {
        return $this->storyItems($story);
    }

    private function storyItems(Story $story): Collection
    {
        return Item::whereIn('ItemId', $story->ItemIds)
            ->orderBy('OrderIndex')
            ->get();
    }
}
