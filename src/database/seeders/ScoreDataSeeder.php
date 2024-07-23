<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScoreDataSeeder extends Seeder
{
    public static $data = [
        [
            'ScoreId'     => 1,
            'ItemId'      => 1,
            'UserId'      => 1,
            'ScoreTypeId' => 2,
            'Amount'      => 55,
            'Timestamp'   => '2021-01-01T12:00:00.000000Z'
        ],
        [
            'ScoreId'     => 2,
            'ItemId'      => 2,
            'UserId'      => 2,
            'ScoreTypeId' => 2,
            'Amount'      => 2,
            'Timestamp'   => '2021-01-05T12:00:00.000000Z'
        ],
        [
            'ScoreId'     => 3,
            'ItemId'      => 3,
            'UserId'      => 1,
            'ScoreTypeId' => 3,
            'Amount'      => 10,
            'Timestamp'   => '2022-02-01T12:00:00.000000Z'
        ]
    ];

    public function run(): void
    {
        DB::table('Score')->insert(self::$data);
    }
}
