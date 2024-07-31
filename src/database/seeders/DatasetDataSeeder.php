<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatasetDataSeeder extends Seeder
{
    public static $data = [
        [
            'DatasetId' => 1,
            'Name'      => 'Dataset-1',
            'ProjectId' => 1
        ],
        [
            'DatasetId' => 2,
            'Name'      => 'Dataset-2',
            'ProjectId' => 2
        ]
    ];

    public function run(): void
    {
        DB::table('Dataset')->insert(self::$data);
    }
}
