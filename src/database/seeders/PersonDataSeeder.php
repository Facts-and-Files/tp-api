<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonDataSeeder extends Seeder
{
    public static $data = [
        [
            'PersonId'         => 1,
            'FirstName'        => 'Max',
            'LastName'         => 'Mustermann',
            'BirthPlace'       => 'Musterstadt',
            'BirthDate'        => '0001-01-01',
            'BirthDateDisplay' => 'Januar 1',
            'DeathPlace'       => 'Musterstadt',
            'DeathDate'        => '2999-12-31',
            'DeathDateDisplay' => 'Spät',
            'Link'             => 'Q11111',
            'Description'      => 'Test Entry',
            'PersonRole'       => 'DocumentCreator'
        ],
        [
            'PersonId'         => 2,
            'FirstName'        => 'Max 2',
            'LastName'         => 'Mustermann 2',
            'BirthPlace'       => 'Musterstadt 2',
            'BirthDate'        => '0001-01-02',
            'BirthDateDisplay' => 'Januar 2',
            'DeathPlace'       => 'Musterstadt 2',
            'DeathDate'        => '3000-12-31',
            'DeathDateDisplay' => 'Sehr spät',
            'Link'             => 'Q11111',
            'Description'      => 'Test Entry 2',
            'PersonRole'       => 'DocumentCreator'
        ]
    ];

    public function run(): void
    {
        DB::table('Person')->insert(self::$data);
    }
}
