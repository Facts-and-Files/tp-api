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
            'TranscriptionSource'   => 'manual',
            'ImageLink'             => '{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif/full/full/0/default.jpg","@type":"dctypes:Image","width":3533,"height":5000,"service":{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif","@context":"http://iiif.io/api/image/2/context.json","profile":"http://iiif.io/api/image/2/level1.json"}}',
        ],
        [
            'ItemId'                => 2,
            'StoryId'               => 2,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocaionStatusId'       => 1,
            'OrderIndex'            => 1,
            'TranscriptionSource'   => 'manual',
            'ImageLink'             => 'Imagelink Item 2',
        ],
        [
            'ItemId'                => 3,
            'StoryId'               => 3,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocaionStatusId'       => 1,
            'OrderIndex'            => 1,
            'TranscriptionSource'   => 'manual',
            'ImageLink'             => 'Imagelink Item 3',
        ],
        [
            'ItemId'                => 5,
            'StoryId'               => 3,
            'CompletionStatusId'    => 1,
            'TranscriptionStatusId' => 1,
            'LocaionStatusId'       => 1,
            'OrderIndex'            => 1,
            'TranscriptionSource'   => 'manual',
            'ImageLink'             => 'Imagelink Item 5',
        ]
    ];

    public function run(): void
    {
        DB::table('Item')->insert(self::$data);
    }
}
