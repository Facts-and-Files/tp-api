<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\Story;
use App\Services\Export\StoryExportManager;
use App\Traits\BuildExportFilename;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoryItemExportController extends ResponseController
{
    use BuildExportFilename;

    public function __construct(private StoryExportManager $storyExportManager) {}

    public function export(int $id, string $format): StreamedResponse|JsonResponse
    {
        $story = Story::findOrFail($id);

        $config = config("exports.formats.{$format}");

        if ($config === null) {
            return $this->sendError('Invvalid Format', "Export format {$format} is not supported.", 422);
        }

        if ($format === 'csv') {
            // overwrite for zipped CSV
            $contentType = config('exports.formats.zip.content_type');
            $extension = config('exports.formats.zip.extension');
            $filename = $this->buildExportFilename(
                $id,
                null,
                null,
                $extension,
            );

            return response()->streamDownload(
                function () use ($story, $format, $extension) {
                    $this->storyExportManager->exportZip($story, $extension);
                },
                $filename,
                [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => "attachment; filename={$filename}",
                ],
            );
        }

        $contentType = $config['content_type'];
        $extension   = $config['extension'];

        $content = $this->storyExportManager->export($story, $format);

        $filename = $this->buildExportFilename(
            $id,
            null,
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
