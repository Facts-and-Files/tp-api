<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HtrDataRevisionDataSeeder extends Seeder
{
    public static $data = [
        [
            'HtrDataRevisionId'   => 1,
            'HtrDataId'           => 1,
            'UserId'              => null,
            'TranscriptionData'   => null,
            'TranscriptionText'   => null,
            'Timestamp'           => '2022-12-15 11:44:31',
            'LastUpdated'         => '2022-12-15 11:44:51',
        ],
        [
            'HtrDataRevisionId'   => 2,
            'HtrDataId'           => 2,
            'UserId'              => null,
            'TranscriptionData'   => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<PcGts xmlns="http://schema.primaresearch.org/PAGE/gts/pagecontent/2013-07-15" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.primaresearch.org/PAGE/gts/pagecontent/2013-07-15 http://schema.primaresearch.org/PAGE/gts/pagecontent/2013-07-15/pagecontent.xsd">
    <Metadata>
        <Creator>Transkribus Processing API</Creator>
        <Created>2023-06-16T17:50:53.222+02:00</Created>
        <LastChange>2023-06-16T17:50:53.222+02:00</LastChange>
    </Metadata>
    <Page imageFilename="orig_proc-api-f1da4dda-ed01-4678-b836-0fed06f12bfd.jpg" imageWidth="4918" imageHeight="7491">
        <ReadingOrder>
            <OrderedGroup id="ro_1686933000354" caption="Regions reading order">
                <RegionRefIndexed index="0" regionRef="tr_1"/>
            </OrderedGroup>
        </ReadingOrder>
        <TextRegion id="tr_1" custom="readingOrder {index:0;}">
            <Coords points="1033,332 1194,332 1194,398 1033,398"/>
            <TextLine id="tr_1_tl_1" custom="readingOrder {index:0;}">
                <Coords points="1024,420 1100,408 1130,422 1192,420 1184,264 1144,258 1136,266 1118,260 1108,268 1102,262 1016,266"/>
                <Baseline points="1033,382 1123,388 1194,388"/>
                <Word id="tr_1_tl_1_w1" custom="readingOrder {index:0;}">
                    <Coords points="1060,344 1060,404 1092,404 1092,344"/>
                    <TextEquiv>
                        <Unicode>X</Unicode>
                    </TextEquiv>
                </Word>
                <TextEquiv>
                    <Unicode>X</Unicode>
                </TextEquiv>
            </TextLine>
            <TextEquiv>
                <Unicode></Unicode>
            </TextEquiv>
        </TextRegion>
    </Page>
</PcGts>',
            'TranscriptionText'   => 'X',
            'Timestamp'           => '2022-12-15 11:54:38',
            'LastUpdated'         => '2022-12-15 11:55:29',
        ],
    ];

    public function run(): void
    {
        DB::table('HtrDataRevision')->insert(self::$data);
    }
}
