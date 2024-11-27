<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemDataSeeder extends Seeder
{
    public static $data = [
        [
            'ItemId'                => 1,
            'StoryId'               => 1,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocationStatusId'      => 1,
            'OrderIndex'            => 1,
            'ImageLink'             => 'Imagelink Item 1'
        ],
        [
            'ItemId'                => 2,
            'StoryId'               => 2,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocaionStatusId'       => 1,
            'OrderIndex'            => 1,
            'ImageLink'             => 'Imagelink Item 2'
        ],
        [
            'ItemId'                => 3,
            'StoryId'               => 3,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocaionStatusId'       => 1,
            'OrderIndex'            => 1,
            'ImageLink'             => 'Imagelink Item 3'
        ],
        [
            'ItemId'                => 5,
            'StoryId'               => 3,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocaionStatusId'       => 1,
            'OrderIndex'            => 1,
            'ImageLink'             => 'Imagelink Item 5'
        ]
    ];

    public function run(): void
    {
        DB::table('Item')->insert(self::$data);
    }
}
