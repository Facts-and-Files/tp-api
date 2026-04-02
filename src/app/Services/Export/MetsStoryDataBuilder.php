<?php

namespace App\Services\Export;

use App\Models\Item;
use App\Models\Story;
use App\Services\Converter\DTO\MetsStoryData;
use App\Services\Converter\DTO\MetsItemData;
use App\Traits\ExtractIiifImageLink;

class MetsStoryDataBuilder
{
    use ExtractIiifImageLink;

    public function __construct(private ItemExportManager $itemExportManager) {}

    public function build(Story $story): MetsStoryData
    {
        $items = Item::whereIn('ItemId', $story->ItemIds)
            ->orderBy('OrderIndex')
            ->get();

        $manifest = $story->Manifest ?: $items->first()?->Manifest;
        $previewImage = $this->extractIiifImageLink($story->PreviewImage);

        $itemData = $items->map(function (Item $item) {
            $altoXml  = $this->itemExportManager->export($item, 'alto');
            $imageLink = $this->extractIiifImageLink($item->ImageLink);

            return new MetsItemData(
                itemId: $item->ItemId,
                order: $item->OrderIndex,
                imageLink: $imageLink,
                altoXml: $altoXml,
            );
        })->all();

        return new MetsStoryData(
            storyId: $story->StoryId,
            dc: $story->Dc,
            dcterms: $story->Dcterms,
            edm: $story->Edm,
            manifest: $manifest,
            previewImage: $previewImage,
            projectName: $story->ProjectName,
            recordId: $story->RecordId,
            timestamp: $story->Timestamp,
            lastUpdated: $story->LastUpdated,
            items: $itemData,
        );
    }
}
