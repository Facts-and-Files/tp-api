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
}
