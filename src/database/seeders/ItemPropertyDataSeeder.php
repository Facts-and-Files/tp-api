<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemPropertyDataSeeder extends Seeder
{
    public static $data = [
        [
            'ItemPropertyId' => 1,
            'PropertyId' => 1,
            'ItemId' => 1,
        ],
        [
            'ItemPropertyId' => 2,
            'PropertyId' => 2,
            'ItemId' => 1,
        ],
    ];

    public function run(): void
    {
        DB::table('ItemProperty')->insert(self::$data);
    }
}
