<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnnotationDataSeeder extends Seeder
{
    public static $data = [
        [
            'AnnotationId' => 1,
            'Text' => '<b>Annotation Text</b>',
            'TextNoTags' => 'Annotation Text',
            'ItemId' => 1,
            'AnnotationTypeId' => 1,
            'EuropeanaAnnotationId' => null,
            'X_Coord' => 10,
            'Y_Coord' => 20,
            'Width' => 100,
            'Height' => 50,
            'Timestamp' => '2025-01-31T12:00:00.000000Z',
        ],
        [
            'AnnotationId' => 2,
            'Text' => '<i>Second Annotation</i>',
            'TextNoTags' => 'Second Annotation',
            'ItemId' => 1,
            'AnnotationTypeId' => 1,
            'EuropeanaAnnotationId' => null,
            'X_Coord' => 30,
            'Y_Coord' => 40,
            'Width' => 120,
            'Height' => 60,
            'Timestamp' => '2025-01-15T12:00:00.000000Z',
        ],
    ];

    public function run(): void
    {
        DB::table('Annotation')->insert(self::$data);
    }
}
