<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HtrDataLanguageDataSeeder extends Seeder
{
    public static $data = [
        [
            'HtrDataId' => 1,
            'LanguageId' => 1,
        ],
        [
            'HtrDataId' => 2,
            'LanguageId' => 2,
        ],
    ];

    public function run(): void
    {
        DB::table('HtrDataLanguage')->insert(self::$data);
    }
}
