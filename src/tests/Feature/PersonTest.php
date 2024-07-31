<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPersonDataSeeder;
use Database\Seeders\PersonDataSeeder;
use Tests\TestCase;

class PersonTest extends TestCase
{
    private static $endpoint = '/persons';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PersonDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemPersonDataSeeder::class]);
    }

    public function testGetAllPersons(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PersonDataSeeder::$data];

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
        $awaitedData = ['data' => [PersonDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPersonsByFirstname(): void
    {
        $queryParams = '?FirstName='. PersonDataSeeder::$data[1]['FirstName'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PersonDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPersonsByLastname(): void
    {
        $queryParams = '?LastName='. PersonDataSeeder::$data[1]['LastName'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PersonDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllPersonsByItemId(): void
    {
        $endpoint = '/items/' . ItemPersonDataSeeder::$data[0]['ItemId'] . '/persons';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PersonDataSeeder::$data];

        $response = $this->get($endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testCreateAPerson(): void
    {
        $createData = [
            'FirstName'  => 'Max 3',
            'PersonRole' => 'DocumentCreator',
            'ItemId'     => 2
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];
        $awaitedData['data']['PersonId'] = 3;
        $awaitedData['data']['ItemIds'] = [2];

        $response = $this->post(self::$endpoint, $createData);

        unset($awaitedData['data']['ItemId']);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testUpdateItemStatusWhenPersonIsInserted(): void
    {
        $this->markTestSkipped('must be revisited.');
    }

    public function testUpdateAPerson(): void
    {
        $updateData = [
            'FirstName'  => 'Max 4'
        ];
        $personId = PersonDataSeeder::$data[1]['PersonId'];
        $queryParams = '/' . $personId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $updateData];

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testDeleteAPerson(): void
    {
        $datasetId = PersonDataSeeder::$data[1]['PersonId'];
        $queryParams = '/' . $datasetId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PersonDataSeeder::$data[1]];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
