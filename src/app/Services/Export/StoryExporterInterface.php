<?php

namespace App\Services\Export;

use App\Models\Story;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface StoryExporterInterface
{
    public function export(Story $data): StreamedResponse;
}
