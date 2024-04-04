<?php

namespace App\Traits;

trait Rename
{
    protected $renameStrings = [
        'Transcription'     => 'ManualTranscriptions',
        'HTR-Transcription' => 'HTRTranscriptions'
    ];

    public function rename(string $name): string
    {
        return $this->renameStrings[$name] ?? $name;
    }
}

