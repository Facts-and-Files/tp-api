<?php

namespace Tests\Feature;

use Tests\TestCase;
// use Illuminate\Support\Facades\DB;

class ImportTest extends TestCase
{
    private static $endpoint = 'import';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testImport(): void
    {

        $importData = [
            [
                'Story' => [
                    'ExternalRecordId' => '',
                    'PlaceName' => '',
                    'PlaceLatitude' => '',
                    'PlaceLongitude' => '',
                    'placeZoom' => '',
                    'PlaceUserGenerated' => 1,
                    'Public' => 1,
                    'ImportName' => '',
                    'ProjectId' => '',
                    'RecordId' => '',
                    'PreviewImage' => '',
                    'DatasetId' => '',
                    'StoryLanguage' => '',
                    // 'PlaceLink' => '',
                    // 'PlaceComment' => '',
                    // 'PlaceUserId' => '',
                    // 'OldStoryId' => '',
                    // 'CompletionStatusId' => '',
                    // 'Summary' => '',
                    // 'ParentStory' => '',
                    // 'SearchText' => '',
                    // 'DateStart' => '',
                    // 'DateEnd' => '',
                    // 'OrderIndex' => '',
                    'Dc' => [
                        'Title' => '',
                        'Description' => '',
                        'Creator' => '',
                        'Source' => '',
                        'Contributor' => '',
                        'Publisher' => '',
                        'Coverage' => '',
                        'Date' => '',
                        'Type' => '',
                        'Relation' => '',
                        'Rights' => '',
                        'Language' => '',
                        'Identifier' => ''
                    ],
                    'Dcterms' => [
                        'Medium' => '',
                        'Provenance' => '',
                        'Created' => ''
                    ],
                    'Edm' => [
                        'LandingPage' => '',
                        'Country' => '',
                        'DataProvider' => '',
                        'Provider' => '',
                        'Rights' => '',
                        'Year' => '',
                        'DatasetName' => '',
                        'Begin' => '',
                        'End' => '',
                        'IsShownAt' => '',
                        'Language' => '',
                        'Agent' => ''
                    ]
                ],
                'Items' => []
            ],
            [
                'Story' => [],
                'Items' => []
            ]
        ];
        $awaitedSuccess = ['success' => true];
        // $awaitedData = ['data' => $createData];

        $response = $this->post(self::$endpoint, $importData);

        $response
            ->assertOk();
            // ->assertJson($awaitedSuccess)
            // ->assertJson($awaitedData);
    }

}
