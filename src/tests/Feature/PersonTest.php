<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class PersonTest extends TestCase
{
    private static $endpoint = '/persons';

    private static $tableData = [
        [
            'PersonId'         => 1,
            'FirstName'        => 'Max',
            'LastName'         => 'Mustermann',
            'BirthPlace'       => 'Musterstadt',
            'BirthDate'        => '0001-01-01',
            'BirthDateDisplay' => 'Januar 1',
            'DeathPlace'       => 'Musterstadt',
            'DeathDate'        => '2999-12-31',
            'DeathDateDisplay' => 'Spät',
            'Link'             => 'Q11111',
            'Description'      => 'Test Entry',
            'PersonRole'       => 'DocumentCreator'
        ],
        [
            'PersonId'         => 2,
            'FirstName'        => 'Max 2',
            'LastName'         => 'Mustermann 2',
            'BirthPlace'       => 'Musterstadt 2',
            'BirthDate'        => '0001-01-02',
            'BirthDateDisplay' => 'Januar 2',
            'DeathPlace'       => 'Musterstadt 2',
            'DeathDate'        => '3000-12-31',
            'DeathDateDisplay' => 'Sehr spät',
            'Link'             => 'Q11111',
            'Description'      => 'Test Entry 2',
            'PersonRole'       => 'DocumentCreator'
        ]
    ];

    private static $itemPersonData = [
        [
            'ItemPersonId' => 1,
            'ItemId'       => 1,
            'PersonId'     => 1
        ],
        [
            'ItemPersonId' => 2,
            'ItemId'       => 1,
            'PersonId'     => 2
        ]
    ];

    private static $itemData = [
        [
            'ItemId'             => 1,
            'CompletionStatusId' => 1,
            'TaggingStatusId'    => 1,
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        DB::table('Item')->insert(self::$itemData);
        DB::table('Person')->insert(self::$tableData);
        DB::table('ItemPerson')->insert(self::$itemPersonData);
    }

    public function testGetAllPersons(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPersonsLimitedAndSorted(): void
    {
        $queryParams = '?limit=1&page=1&orderBy=PersonId&orderDir=desc';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPersonsByFirstname(): void
    {
        $queryParams = '?FirstName='. self::$tableData[1]['FirstName'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPersonsByLastname(): void
    {
        $queryParams = '?LastName='. self::$tableData[1]['LastName'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testCreateAPerson(): void
    {
        $this->markTestSkipped('must be revisited.');

    }

    public function testUpdateAPerson(): void
    {
        $this->markTestSkipped('must be revisited.');

    }

    public function testDeleteAPerson(): void
    {
        $datasetId = self::$tableData[1]['PersonId'];
        $queryParams = '/' . $datasetId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData[1]];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
