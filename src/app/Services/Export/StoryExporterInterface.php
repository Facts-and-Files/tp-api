<?php

namespace App\Services\Export;

use App\Models\Story;

interface StoryExporterInterface
{
    public function export(Story $data): mixed;
}
