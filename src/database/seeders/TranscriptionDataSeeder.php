<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranscriptionDataSeeder extends Seeder
{
    public static $data = [
        [
            'TranscriptionId' => 1,
            'Text' => '<b>Example Text</b>',
            'TextNoTags' => 'Example Text',
            'UserId' => 1,
            'ItemId' => 1,
            'NoText' => false,
            'CurrentVersion' => true,
            'EuropeanaAnnotationId' => null,
            'Timestamp' => '2025-01-31T12:00:00.000000Z'
        ],
        [
            'TranscriptionId' => 2,
            'Text' => '<b>Example Text</b>',
            'TextNoTags' => 'Example Text',
            'UserId' => 1,
            'ItemId' => 1,
            'NoText' => false,
            'CurrentVersion' => false,
            'EuropeanaAnnotationId' => null,
            'Timestamp' => '2025-01-01T12:00:00.000000Z'
        ],
        [
            'TranscriptionId' => 3,
            'Text' => '',
            'TextNoTags' => '',
            'UserId' => 2,
            'ItemId' => 2,
            'NoText' => true,
            'CurrentVersion' => true,
            'EuropeanaAnnotationId' => null,
            'Timestamp' => '2025-01-15T12:00:00.000000Z'
        ],
        [
            'TranscriptionId' => 4,
            'Text' => '<b>Example Text</b>',
            'TextNoTags' => 'Example Text',
            'UserId' => 3,
            'ItemId' => 3,
            'NoText' => false,
            'CurrentVersion' => true,
            'EuropeanaAnnotationId' => null,
            'Timestamp' => '2025-01-15T12:00:00.000000Z'
        ],
    ];

    public function run(): void
    {
        DB::table('Transcription')->insert(self::$data);
    }
}
