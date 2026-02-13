<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranscriptionLanguageDataSeeder extends Seeder
{
    public static $data = [
        [
            'TranscriptionLanguageId' => 1,
            'TranscriptionId' => 1,
            'LanguageId' => 1,
        ],
        [
            'TranscriptionLanguageId' => 2,
            'TranscriptionId' => 2,
            'LanguageId' => 1,
        ],
        [
            'TranscriptionLanguageId' => 3,
            'TranscriptionId' => 3,
            'LanguageId' => 1,
        ],
        [
            'TranscriptionLanguageId' => 4,
            'TranscriptionId' => 1,
            'LanguageId' => 2,
        ],
        [
            'TranscriptionLanguageId' => 5,
            'TranscriptionId' => 4,
            'LanguageId' => 1,
        ],
        [
            'TranscriptionLanguageId' => 6,
            'TranscriptionId' => 4,
            'LanguageId' => 2,
        ],
    ];

    public function run(): void
    {
        DB::table('TranscriptionLanguage')->insert(self::$data);
    }
}
