<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoryDataSeeder extends Seeder
{
    public static $data = [
        [
            'StoryId' => 1,
            'Manifest' => 'http://example.com/manifest1.json',
            'ExternalRecordId' => '',
            'PlaceName' => '',
            'PlaceLatitude' => '',
            'PlaceLongitude' => '',
            'placeZoom' => '',
            'PlaceUserGenerated' => 1,
            'Public' => 1,
            'ImportName' => '',
            'ProjectId' => 1,
            'RecordId' => '',
            'PreviewImage' => '',
            'DatasetId' => '',
            'StoryLanguage' => '',
            'PlaceLink' => '',
            'PlaceComment' => '',
            'DateStart' => '',
            'DateEnd' => '',
            'dc:title' => '',
            'dc:description' => '',
            'dc:creator' => '',
            'dc:source' => '',
            'dc:contributor' => '',
            'dc:publisher' => '',
            'dc:coverage' => '',
            'dc:date' => '',
            'dc:type' => '',
            'dc:relation' => '',
            'dc:rights' => '',
            'dc:language' => '',
            'dc:identifier' => '',
            'dcterms:medium' => '',
            'dcterms:provenance' => '',
            'dcterms:created' => '',
            'edm:landingPage' => '',
            'edm:country' => '',
            'edm:dataProvider' => '',
            'edm:provider' => '',
            'edm:rights' => '',
            'edm:year' => '',
            'edm:datasetName' => '',
            'edm:begin' => '',
            'edm:end' => '',
            'edm:isShownAt' => '',
            'edm:language' => '',
            'edm:agent' => ''
        ],
        [
            'StoryId' => 2,
            'Manifest' => 'http://example.com/manifest2.json',
            'ExternalRecordId' => '',
            'PlaceName' => '',
            'PlaceLatitude' => '',
            'PlaceLongitude' => '',
            'placeZoom' => '',
            'PlaceUserGenerated' => 1,
            'Public' => 1,
            'ImportName' => '',
            'ProjectId' => 1,
            'RecordId' => '',
            'PreviewImage' => '',
            'DatasetId' => '',
            'StoryLanguage' => '',
            'PlaceLink' => '',
            'PlaceComment' => '',
            'DateStart' => '',
            'DateEnd' => '',
            'dc:title' => '',
            'dc:description' => '',
            'dc:creator' => '',
            'dc:source' => '',
            'dc:contributor' => '',
            'dc:publisher' => '',
            'dc:coverage' => '',
            'dc:date' => '',
            'dc:type' => '',
            'dc:relation' => '',
            'dc:rights' => '',
            'dc:language' => '',
            'dc:identifier' => '',
            'dcterms:medium' => '',
            'dcterms:provenance' => '',
            'dcterms:created' => '',
            'edm:landingPage' => '',
            'edm:country' => '',
            'edm:dataProvider' => '',
            'edm:provider' => '',
            'edm:rights' => '',
            'edm:year' => '',
            'edm:datasetName' => '',
            'edm:begin' => '',
            'edm:end' => '',
            'edm:isShownAt' => '',
            'edm:language' => '',
            'edm:agent' => ''
        ],
        [
            'StoryId' => 3,
            'Manifest' => 'http://example.com/manifest3.json',
            'ExternalRecordId' => '',
            'PlaceName' => '',
            'PlaceLatitude' => '',
            'PlaceLongitude' => '',
            'placeZoom' => '',
            'PlaceUserGenerated' => 1,
            'Public' => 1,
            'ImportName' => '',
            'ProjectId' => 2,
            'RecordId' => '',
            'PreviewImage' => '',
            'DatasetId' => '',
            'StoryLanguage' => '',
            'PlaceLink' => '',
            'PlaceComment' => '',
            'DateStart' => '',
            'DateEnd' => '',
            'dc:title' => '',
            'dc:description' => '',
            'dc:creator' => '',
            'dc:source' => '',
            'dc:contributor' => '',
            'dc:publisher' => '',
            'dc:coverage' => '',
            'dc:date' => '',
            'dc:type' => '',
            'dc:relation' => '',
            'dc:rights' => '',
            'dc:language' => '',
            'dc:identifier' => '',
            'dcterms:medium' => '',
            'dcterms:provenance' => '',
            'dcterms:created' => '',
            'edm:landingPage' => '',
            'edm:country' => '',
            'edm:dataProvider' => '',
            'edm:provider' => '',
            'edm:rights' => '',
            'edm:year' => '',
            'edm:datasetName' => '',
            'edm:begin' => '',
            'edm:end' => '',
            'edm:isShownAt' => '',
            'edm:language' => '',
            'edm:agent' => ''
        ]
    ];

    public function run(): void
    {
        DB::table('Story')->insert(self::$data);
    }
}
