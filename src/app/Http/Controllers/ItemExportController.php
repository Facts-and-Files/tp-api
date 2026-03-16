<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\Item;
use App\Services\Export\ItemExportManager;
use App\Traits\BuildExportFilename;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemExportController extends ResponseController
{
    use BuildExportFilename;

    public function __construct(private ItemExportManager $itemExportManager) {}

    public function export(int $id, string $format): StreamedResponse|JsonResponse
    {
        $item = Item::findOrFail($id);

        $config = config("exports.formats.{$format}");

        if ($config === null) {
            return $this->sendError('Invvalid Format', "Export format {$format} is not supported.", 422);
        }

        $contentType = $config['content_type'];
        $extension   = $config['extension'];

        $content = $this->itemExportManager->export($item, $format);
        $filename = $this->buildExportFilename(
            ($item['StoryId'] ?? 'unknown'),
            $id,
            null,
            $extension,
        );

        return response()->streamDownload(
            callback: function () use ($content) {
                echo $content;
            },
            name: $filename,
            headers: [
                'Content-Type' => $contentType,
                'Content-Disposition' => "attachment; filename={$filename}",
            ]
        );
    }
}
