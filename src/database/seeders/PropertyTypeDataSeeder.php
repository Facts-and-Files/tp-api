<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertyTypeDataSeeder extends Seeder
{
    public static $data = [
        [
            'PropertyTypeId' => 1,
            'Name'           => 'Language',
        ],
        [
            'PropertyTypeId' => 2,
            'Name'           => 'Category',
        ],
    ];

    public function run(): void
    {
        DB::table('PropertyType')->insert(self::$data);
    }
}
