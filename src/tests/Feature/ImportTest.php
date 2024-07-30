<?php

namespace Tests\Feature;

use Tests\TestCase;

class ImportTest extends TestCase
{
    private static $endpoint = 'import';

    private static $importData = [
        [
            'Story' => [
                'ExternalRecordId' => 'TestRecordId1',
                'PlaceName' => '',
                'PlaceLatitude' => '',
                'PlaceLongitude' => '',
                'placeZoom' => '',
                'PlaceUserGenerated' => 1,
                'Public' => 1,
                'ImportName' => '',
                'ProjectId' => 1,
                'RecordId' => null,
                'PreviewImage' => '',
                'DatasetId' => 1,
                'StoryLanguage' => '',
                'PlaceLink' => '',
                'PlaceComment' => '',
                'DateStart' => '',
                'DateEnd' => '',
                'Dc' => [
                    'Title' => 'TestTitle1',
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
            'Items' => [
                [
                    'Title' => 'Test Story 1 Item 1',
                    'Description' => '',
                    'ImageLink' => 'ImageLink 1',
                    'OrderIndex' => 1,
                    'Manifest' => '',
                    'edm:WebResource' => ''
                ],
                [
                    'Title' => 'Test Story 1 Item 2',
                    'Description' => '',
                    'ImageLink' => 'ImageLink 2',
                    'OrderIndex' => 2,
                    'Manifest' => '',
                    'edm:WebResource' => ''
                ]
            ]
        ],
        [
            'Story' => [
                'ExternalRecordId' => 'TestRecordId2',
                'PlaceName' => '',
                'PlaceLatitude' => '',
                'PlaceLongitude' => '',
                'placeZoom' => '',
                'PlaceUserGenerated' => 1,
                'Public' => 1,
                'ImportName' => '',
                'ProjectId' => 1,
                'RecordId' => null,
                'PreviewImage' => '',
                'DatasetId' => 1,
                'StoryLanguage' => '',
                'PlaceLink' => '',
                'PlaceComment' => '',
                'DateStart' => '',
                'DateEnd' => '',
                'Dc' => [
                    'Title' => 'TestTitle2',
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
            'Items' => [
                [
                    'Title' => 'Test Story 2 Item 1',
                    'Description' => '',
                    'ImageLink' => 'Image Link',
                    'OrderIndex' => 1,
                    'Manifest' => '',
                    'edm:WebResource' => ''
                ]
            ]
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testImport(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' =>
            [
                [
                    'StoryId' => 1,
                    'ExternalRecordId' => self::$importData[0]['Story']['ExternalRecordId'],
                    'RecordId' => self::$importData[0]['Story']['RecordId'],
                    'dc:title' => self::$importData[0]['Story']['Dc']['Title'],
                ],
                [
                    'StoryId' => 2,
                    'ExternalRecordId' => self::$importData[1]['Story']['ExternalRecordId'],
                    'RecordId' => self::$importData[1]['Story']['RecordId'],
                    'dc:title' => self::$importData[1]['Story']['Dc']['Title'],
                ]
            ]
        ];

        $response = $this->post(self::$endpoint, self::$importData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testPartialSuccessfulStoryImport(): void
    {
        $partialImportData = self::$importData;
        $partialImportData[1]['Story']['Dc']['Title'] = null;
        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                [
                    'StoryId' => 1,
                    'ExternalRecordId' => self::$importData[0]['Story']['ExternalRecordId'],
                    'RecordId' => self::$importData[0]['Story']['RecordId'],
                    'dc:title' => self::$importData[0]['Story']['Dc']['Title'],
                ],
            ],
            'error' => [
                [
                    'ExternalRecordId' => $partialImportData[1]['Story']['ExternalRecordId'],
                    'RecordId' => $partialImportData[1]['Story']['RecordId'],
                    'dc:title' => $partialImportData[1]['Story']['Dc']['Title'],
                    'error'    => []
                ]
            ]
        ];

        $response = $this->post(self::$endpoint, $partialImportData);

        $response
            ->assertStatus(207)
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testPartialSuccessfulItemImport(): void
    {
        $partialImportData = self::$importData;
        $partialImportData[0]['Items'][1]['ImageLink'] = null;
        $awaitedSuccess = ['success' => true];
        $awaitedData = [
            'data' => [
                [
                    'StoryId' => 1,
                    'ExternalRecordId' => self::$importData[0]['Story']['ExternalRecordId'],
                    'RecordId' => self::$importData[0]['Story']['RecordId'],
                    'dc:title' => self::$importData[0]['Story']['Dc']['Title'],
                ],
                [
                    'StoryId' => 2,
                    'ExternalRecordId' => self::$importData[1]['Story']['ExternalRecordId'],
                    'RecordId' => self::$importData[1]['Story']['RecordId'],
                    'dc:title' => self::$importData[1]['Story']['Dc']['Title'],
                ],
            ],
            'error' => [
                [
                    'ExternalRecordId' => $partialImportData[0]['Story']['ExternalRecordId'],
                    'RecordId' => $partialImportData[0]['Story']['RecordId'],
                    'ItemOrderIndex' => 2,
                    'error'    => []
                ]
            ]
        ];

        $response = $this->post(self::$endpoint, $partialImportData);

        $response
            ->assertStatus(207)
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
