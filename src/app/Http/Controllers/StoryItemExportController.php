<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ResponseController;
use App\Models\Story;
use App\Services\Export\StoryExportManager;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoryItemExportController extends ResponseController
{
    public function __construct(private StoryExportManager $storyExportManager) {}

    public function export(int $id, string $format): StreamedResponse
    {
        $story = Story::findOrFail($id);

        return $this->storyExportManager->export($story, $format);
    }
}
