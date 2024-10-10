<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Feature\ProjectTest;

class ImportTest extends TestCase
{
    private static $endpoint = 'import';

    private static $importData = [
        [
            'Story' => [
                'ExternalRecordId' => null,
                'RecordId' => 'RecordId1',
                'Manifest' => 'http://example.com/manifest/Record1.json',
                'PlaceName' => '',
                'PlaceLatitude' => '',
                'PlaceLongitude' => '',
                'placeZoom' => '',
                'PlaceUserGenerated' => 1,
                'Public' => 1,
                'ImportName' => '',
                'ProjectId' => 1,
                'PreviewImage' => '',
                'DatasetId' => 1,
                'StoryLanguage' => '',
                'PlaceLink' => '',
                'PlaceComment' => '',
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
                    'ProjectItemId' => 'ExternalId1',
                    'Title' => 'Test Story 1 Item 1',
                    'ImageLink' => 'ImageLink 1',
                    'OrderIndex' => 1
                ],
                [
                    'ProjectItemId' => 'ExternalId2',
                    'Title' => 'Test Story 1 Item 2',
                    'ImageLink' => 'ImageLink 2',
                    'OrderIndex' => 2
                ]
            ]
        ],
        [
            'Story' => [
                'ExternalRecordId' => null,
                'RecordId' => 'TestRecordId2',
                'Manifest' => 'http://example.com/manifest/Record1.json',
                'PlaceName' => '',
                'PlaceLatitude' => '',
                'PlaceLongitude' => '',
                'placeZoom' => '',
                'PlaceUserGenerated' => 1,
                'Public' => 1,
                'ImportName' => '',
                'ProjectId' => 1,
                'PreviewImage' => '',
                'DatasetId' => 1,
                'StoryLanguage' => '',
                'PlaceLink' => '',
                'PlaceComment' => '',
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
                    'ProjectItemId' => 'ExternalId12',
                    'Title' => 'Test Story 2 Item 1',
                    'ImageLink' => 'Image Link',
                    'OrderIndex' => 1
                ]
            ]
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        ProjectTest::populateTable();
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
                    'ProjectItemId' => $partialImportData[0]['Items'][1]['ProjectItemId'],
                    'ItemOrderIndex' => $partialImportData[0]['Items'][1]['OrderIndex'],
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
