<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoryCampaignDataSeeder extends Seeder
{
    public static $data = [
        [
            'CampaignId' => 1,
            'StoryId'    => 1
        ],
        [
            'CampaignId' => 1,
            'StoryId'    => 2
        ],
        [
            'CampaignId' => 2,
            'StoryId'    => 3
        ]
    ];

    public function run(): void
    {
        DB::table('StoryCampaign')->insert(self::$data);
    }
}
