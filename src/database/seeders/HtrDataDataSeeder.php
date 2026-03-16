<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HtrDataDataSeeder extends Seeder
{
    public static $data = [
        [
            'HtrDataId'                => 1,
            'ItemId'                   => 6,
            'HtrProcessId'             => '3962171',
            'HtrModelId'               => 39995,
            'HtrStatus'                => 'FINISHED',
            'EuropeanaAnnotationId'    => null,
            'TranscriptionProviderId'  => 1,
            'Timestamp'                => '2022-12-15 11:44:31',
            'LastUpdated'              => '2022-12-15 11:44:51',
        ],
        [
            'HtrDataId'                => 2,
            'ItemId'                   => 7,
            'HtrProcessId'             => '3962242',
            'HtrModelId'               => 45358,
            'HtrStatus'                => 'FINISHED',
            'EuropeanaAnnotationId'    => null,
            'TranscriptionProviderId'  => 2,
            'Timestamp'                => '2022-12-15 11:54:38',
            'LastUpdated'              => '2022-12-15 11:55:29',
        ],
    ];

    public function run(): void
    {
        DB::table('HtrData')->insert(self::$data);
    }
}
