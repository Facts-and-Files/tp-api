<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Tests\TestCase;

class ItemTest extends TestCase
{
    private static $endpoint = 'items';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => LanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionLanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
    }

    public function test_get_all_items(): void
    {
        $this->markTestSkipped('must be revisited.');
    //     $awaitedSuccess = ['success' => true];
    //     $awaitedData = ['data' => ItemDataSeeder::$data];
    //
    //     $response = $this->get(self::$endpoint);
    //
    //     print_r($response['data']);
    //
    //     $response
    //         ->assertOk()
    //         ->assertJson($awaitedSuccess)
    //         ->assertJson($awaitedData);
    }
}
