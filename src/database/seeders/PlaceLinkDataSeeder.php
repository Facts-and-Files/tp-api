<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaceLinkDataSeeder extends Seeder
{
    public static array $data = [
        [
            'PlaceLinkId' => 1,
            'PlaceId' => 1,
            'Provider' => 'Wikidata',
            'Url' => 'https://www.wikidata.org/wiki/Q777',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ],
        [
            'PlaceLinkId' => 2,
            'PlaceId' => 1,
            'Provider' => 'Wikipedia',
            'Url' => 'https://en.wikipedia.org/wiki/TestStadt',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ],
        [
            'PlaceLinkId' => 3,
            'PlaceId' => 2,
            'Provider' => 'Wikidata',
            'Url' => 'https://www.wikidata.org/wiki/Q778',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ],
    ];

    public function run(): void
    {
        DB::table('PlaceLink')->insert(self::$data);
    }
}
