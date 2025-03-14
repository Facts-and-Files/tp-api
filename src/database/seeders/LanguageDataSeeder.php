<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageDataSeeder extends Seeder
{
    public static $data = [
        [
            'LanguageId'  => '1',
            'Name'        => 'Deutsch',
            'NameEnglish' => 'German',
            'ShortName'   => 'DE',
            'Code'        => 'de',
            'Code3'       => 'deu',
        ],
        [
            'LanguageId'  => '2',
            'Name'        => 'Englisch',
            'NameEnglish' => 'English',
            'ShortName'   => 'EN',
            'Code'        => 'en',
            'Code3'       => 'eng',
        ],
    ];

    public function run(): void
    {
        DB::table('Language')->insert(self::$data);
    }
}
