<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertyDataSeeder extends Seeder
{
    public static $data = [
        [
            'PropertyId'     => 1,
            'Value'          => 'German',
            'PropertyTypeId' => 1
        ],
        [
            'PropertyId'     => 2,
            'Value'          => 'Handwritten',
            'PropertyTypeId' => 2
        ],
    ];

    public function run(): void
    {
        DB::table('Property')->insert(self::$data);
    }
}
