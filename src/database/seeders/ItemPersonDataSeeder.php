<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemPersonDataSeeder extends Seeder
{
    public static $data = [
        [
            'ItemPersonId' => 1,
            'ItemId'       => 1,
            'PersonId'     => 1
        ],
        [
            'ItemPersonId' => 2,
            'ItemId'       => 1,
            'PersonId'     => 2
        ]
    ];

    public function run(): void
    {
        DB::table('ItemPerson')->insert(self::$data);
    }
}
