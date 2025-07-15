<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    private static $endpoint = 'properties';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => PropertyTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyDataSeeder::class]);
    }

    public function test_get_all_properties(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PropertyDataSeeder::$data];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_properties_by_property_type(): void
    {
        $queryParams = '?PropertyTypeId='. PropertyTypeDataSeeder::$data[1]['PropertyTypeId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [PropertyDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_a_nonexistent_property_return_404(): void
    {
        $queryParams = '/100000001';
        $awaitedSuccess = ['success' => false];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function test_get_a_single_property(): void
    {
        $queryParams = '/'. PropertyDataSeeder::$data[0]['PropertyId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PropertyDataSeeder::$data[0]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_create_a_property_with_missing_fields_returns_422(): void
    {
        $createData = [
            'Description' => 'Test-Description',
        ];
        $awaitedSuccess = ['success' => false];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertStatus(422)
            ->assertJson($awaitedSuccess);
    }

    public function test_create_a_property(): void
    {
        $createData = [
            'Value' => 'Test-Value',
            'PropertyTypeId' => 1,
            'Description' => 'Test-Description',
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_update_a_property_with_missing_fields_returns_422(): void
    {
        $queryParams = '/' . PropertyDataSeeder::$data[0]['PropertyId'];
        $updateData = [
            'Description' => 'Test-Description',
        ];
        $awaitedSuccess = ['success' => false];

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertStatus(422)
            ->assertJson($awaitedSuccess);
    }

    public function test_update_a_non_exitent_property_returns_404(): void
    {
        $queryParams = '/100000001';
        $updateData = [
            'Description' => 'Test-Description',
        ];
        $awaitedSuccess = ['success' => false];

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function test_update_a_property(): void
    {
        $queryParams = '/' . PropertyDataSeeder::$data[0]['PropertyId'];
        $updateData = [
            'Value' => 'Test-Value',
            'PropertyTypeId' => 1,
            'Description' => 'Test-Description',
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $updateData];

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_delete_a_non_existent_property_returns_404(): void
    {
        $queryParams = '/100000001';
        $awaitedSuccess = ['success' => false];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function test_delete_a_property(): void
    {
        $queryParams = '/' . PropertyDataSeeder::$data[0]['PropertyId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => PropertyDataSeeder::$data[0]];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
