<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaignDataSeeder extends Seeder
{
    public static $data = [
        [
            'CampaignId' => 1
        ],
        [
            'CampaignId' => 2
        ]
    ];

    public function run(): void
    {
        DB::table('Campaign')->insert(self::$data);
    }
}
