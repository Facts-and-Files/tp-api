<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScoreTypeDataSeeder extends Seeder
{
    public static $data = [
        [
            'ScoreTypeId' => 1,
            'Name'        => 'Location',
            'Rate'        => 0.2
        ],
        [
            'ScoreTypeId' => 2,
            'Name'        => 'Transcription',
            'Rate'        => 0.0033
        ],
        [
            'ScoreTypeId' => 3,
            'Name'        => 'Enrichment',
            'Rate'        => 0.2
        ],
        [
            'ScoreTypeId' => 4,
            'Name'        => 'Description',
            'Rate'        => 0.2
        ],
        [
            'ScoreTypeId' => 5,
            'Name'        => 'HTR-Transcription',
            'Rate'        => 0.0033
        ]
    ];

    public function run(): void
    {
        DB::table('ScoreType')->insertOrIgnore(self::$data);
    }
}
