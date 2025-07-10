<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertyDataSeeder extends Seeder
{
    public static $data = [
        [
            'PropertyId' => 1,
            'Value'  => 'German',
            'Description' => 'Description for Test Propery 1',
            'PropertyTypeId' => 1,
        ],
        [
            'PropertyId' => 2,
            'Value' => 'Handwritten',
            'Description' => 'Description for Test Propery 2',
            'PropertyTypeId' => 2,
        ],
    ];

    public function run(): void
    {
        DB::table('Property')->insert(self::$data);
    }
}
