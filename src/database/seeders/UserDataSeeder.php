<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserDataSeeder extends Seeder
{
    public static $data = [
        [
            'UserId'    => 1,
            'WP_UserId' => 1001,
            'Timestamp'   => '2020-01-01 12:00:00'
        ],
        [
            'UserId'    => 2,
            'WP_UserId' => 1002,
            'Timestamp'   => '2021-01-01 12:00:00'
        ],
        [
            'UserId'    => 3,
            'WP_UserId' => 1003,
            'Timestamp'   => '2022-01-01 12:00:00'
        ],
    ];

    public function run(): void
    {
        DB::table('User')->insert(self::$data);
    }
}
