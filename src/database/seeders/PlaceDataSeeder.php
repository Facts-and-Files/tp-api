<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaceDataSeeder extends Seeder
{
    public static $data = [
        [
            'PlaceId'       => 1,
            'Name'          => 'TestStadt',
            'Latitude'      => 78,
            'Longitude'     => 21,
            'ItemId'        => 1,
            'Link'          => 'link',
            'Comment'       => 'TestComment',
            'UserGenerated' => true,
            'UserId'        => 1,
            'WikidataName'  => 'Teststadt',
            'WikidataId'    => 'Q777',
            'PlaceRole'     => 'CreationPlace'
        ],
        [
            'PlaceId'       => 2,
            'Name'          => 'TestStadt 2',
            'Latitude'      => 80,
            'Longitude'     => 19,
            'ItemId'        => 1,
            'Link'          => 'link',
            'Comment'       => 'TestComment',
            'UserGenerated' => true,
            'UserId'        => 1,
            'WikidataName'  => 'Teststadt',
            'WikidataId'    => 'Q778',
            'PlaceRole'     => 'Other'
        ]
    ];

    public function run(): void
    {
        DB::table('Place')->insert(self::$data);
    }
}
