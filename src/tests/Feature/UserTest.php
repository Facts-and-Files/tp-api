<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\UserDataSeeder;
use Tests\TestCase;

class UserTest extends TestCase
{
    private static $endpoint = '/users';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable(): void
    {
        Artisan::call('db:seed', ['--class' => UserDataSeeder::class]);
    }

    public function testGetOneWpuserIdByByUserId(): void
    {
        $queryParams = '/wpuserids?UserId='. UserDataSeeder::$data[0]['UserId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [UserDataSeeder::$data[0]]];
        unset($awaitedData['data'][0]['Timestamp']);

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetMultipleWpuserIdsByByUserIds(): void
    {
        $queryParams = '/wpuserids?UserId='
            . UserDataSeeder::$data[1]['UserId']
            . ','
            . UserDataSeeder::$data[2]['UserId']
            . '&separator=,';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => UserDataSeeder::$data];
        unset($awaitedData['data'][1]['Timestamp']);
        unset($awaitedData['data'][2]['Timestamp']);
        unset($awaitedData['data'][0]);
        $awaitedData['data'] = array_values($awaitedData['data']);

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
