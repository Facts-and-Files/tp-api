<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompletionStatusDataSeeder extends Seeder
{
    public static $data = [
        [
            'CompletionStatusId' => 1,
            'Name'               => 'Not Started',
            'ColorCode'          => '#eeeeee',
            'ColorCodeGradient'  => '#eeeeee'
        ],
        [
            'CompletionStatusId' => 2,
            'Name'               => 'Edit',
            'ColorCode'          => '#fff700',
            'ColorCodeGradient'  => '#ffd800'
        ],
        [
            'CompletionStatusId' => 3,
            'Name'               => 'Review',
            'ColorCode'          => '#ffc720',
            'ColorCodeGradient'  => '#f0b146'
        ],
        [
            'CompletionStatusId' => 4,
            'Name'               => 'Completed',
            'ColorCode'          => '#61e02f',
            'ColorCodeGradient'  => '#4dcd1c'
        ]
    ];

    public function run(): void
    {
        DB::table('CompletionStatus')->insert(self::$data);
    }
}
