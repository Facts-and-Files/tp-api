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
            'DescriptionStatusId'   => 1,
            'TranscriptionStatusId' => 1,
            'LocationStatusId'      => 1,
            'TaggingStatusId'       => 1,
            'OrderIndex'            => 1,
            'TranscriptionSource'   => 'manual',
            'ImageLink'             => '{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif/full/full/0/default.jpg","@type":"dctypes:Image","width":3533,"height":5000,"service":{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif","@context":"http://iiif.io/api/image/2/context.json","profile":"http://iiif.io/api/image/2/level1.json"}}',
            'DescriptionLanguage'   => 1,
            'Description'           => 'Test Description Item 1',
            'Manifest'              => 'http://example.com/manifest1fromItem.json',
        ],
        [
            'ItemId'                => 2,
            'StoryId'               => 2,
            'CompletionStatusId'    => 1,
            'DescriptionStatusId'   => 1,
            'TranscriptionStatusId' => 1,
            'LocationStatusId'      => 1,
            'TaggingStatusId'       => 1,
            'OrderIndex'            => 1,
            'TranscriptionSource'   => 'manual',
            'ImageLink'             => '{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif/full/full/0/default.jpg","@type":"dctypes:Image","width":3533,"height":5000,"service":{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif","@context":"http://iiif.io/api/image/2/context.json","profile":"http://iiif.io/api/image/2/level1.json"}}',
            'DescriptionLanguage'   => 1,
            'Description'           => 'Test Description Item 2',
            'Manifest'              => '',
        ],
        [
            'ItemId'                => 3,
            'StoryId'               => 3,
            'CompletionStatusId'    => 1,
            'DescriptionStatusId'   => 1,
            'TranscriptionStatusId' => 1,
            'LocationStatusId'      => 1,
            'TaggingStatusId'       => 1,
            'OrderIndex'            => 1,
            'TranscriptionSource'   => 'manual',
            'ImageLink'             => '{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif/full/full/0/default.jpg","@type":"dctypes:Image","width":3533,"height":5000,"service":{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif","@context":"http://iiif.io/api/image/2/context.json","profile":"http://iiif.io/api/image/2/level1.json"}}',
            'DescriptionLanguage'   => 1,
            'Description'           => 'Test Description Item 3',
            'Manifest'              => '',
        ],
        [
            'ItemId'                => 5,
            'StoryId'               => 3,
            'CompletionStatusId'    => 1,
            'DescriptionStatusId'   => 1,
            'TranscriptionStatusId' => 1,
            'LocationStatusId'      => 1,
            'TaggingStatusId'       => 1,
            'OrderIndex'            => 1,
            'TranscriptionSource'   => 'manual',
            'ImageLink'             => '{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif/full/full/0/default.jpg","@type":"dctypes:Image","width":3533,"height":5000,"service":{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif","@context":"http://iiif.io/api/image/2/context.json","profile":"http://iiif.io/api/image/2/level1.json"}}',
            'DescriptionLanguage'   => 1,
            'Description'           => 'Test Description Item 5',
            'Manifest'              => '',
        ],
        [
            'ItemId'                => 6,
            'StoryId'               => 2,
            'CompletionStatusId'    => 1,
            'DescriptionStatusId'   => 1,
            'TranscriptionStatusId' => 1,
            'LocationStatusId'      => 1,
            'TaggingStatusId'       => 1,
            'OrderIndex'            => 1,
            'TranscriptionSource'   => 'htr',
            'ImageLink'             => '{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif/full/full/0/default.jpg","@type":"dctypes:Image","width":3533,"height":5000,"service":{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif","@context":"http://iiif.io/api/image/2/context.json","profile":"http://iiif.io/api/image/2/level1.json"}}',
            'DescriptionLanguage'   => 2,
            'Description'           => 'Test Description Item 6',
            'Manifest'              => '',
        ],
        [
            'ItemId'                => 7,
            'StoryId'               => 2,
            'CompletionStatusId'    => 1,
            'DescriptionStatusId'   => 1,
            'TranscriptionStatusId' => 1,
            'LocationStatusId'      => 1,
            'TaggingStatusId'       => 1,
            'OrderIndex'            => 1,
            'TranscriptionSource'   => 'htr',
            'ImageLink'             => '{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif/full/full/0/default.jpg","@type":"dctypes:Image","width":3533,"height":5000,"service":{"@id":"rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=1//2025903/_nnVvTgs/PAN044_Page0000.tif","@context":"http://iiif.io/api/image/2/context.json","profile":"http://iiif.io/api/image/2/level1.json"}}',
            'DescriptionLanguage'   => 2,
            'Description'           => 'Test Description Item 7',
            'Manifest'              => '',
        ],
    ];

    public function run(): void
    {
        DB::table('Item')->insert(self::$data);
    }
}
