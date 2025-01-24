<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\TranscriptionDataSeeder;
use Tests\TestCase;

class TranscriptionTest extends TestCase
{
    private static $endpoint = 'transcriptions';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => TranscriptionDataSeeder::class]);
    }

    public function test_get_all_transcriptions(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => TranscriptionDataSeeder::$data];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_transcriptions_limited_and_sorted(): void
    {
        $queryParams = '?limit=1&page=1&orderBy=Timestamp&orderDir=desc';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [TranscriptionDataSeeder::$data[0]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_transcriptions_by_itemid(): void
    {
        $queryParams = '?ItemId='. TranscriptionDataSeeder::$data[2]['ItemId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [TranscriptionDataSeeder::$data[2]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_all_transcription_by_itemid_and_currentversion(): void
    {
        $queryParams = '?ItemId='. TranscriptionDataSeeder::$data[0]['ItemId']
            . '&CurrentVersion=true';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [TranscriptionDataSeeder::$data[0]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_a_single_transcription(): void
    {
        $queryParams = '/'. TranscriptionDataSeeder::$data[1]['TranscriptionId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => TranscriptionDataSeeder::$data[1]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function test_get_a_non_existent_single_transcription_returns_404(): void
    {
        $queryParams = '/10000000';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => TranscriptionDataSeeder::$data[1]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response->assertStatus(404);
    }

    public function test_create_a_transcription_with_no_itemid_returns_400(): void
    {
        $createData = [
            'Text' => '<p>People Wall</p>',
            'TextNoTags' => 'People Wall',
            'UserId' => 3,
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];

        $response = $this->post(self::$endpoint, $createData);

        $response->assertStatus(400);
    }

    public function test_create_a_transcription_with_no_userid_returns_400(): void
    {
        $createData = [
            'Text' => '<p>People Wall</p>',
            'TextNoTags' => 'People Wall',
            'ItemId' => 3,
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];

        $response = $this->post(self::$endpoint, $createData);

        $response->assertStatus(400);
    }

    public function test_create_a_transcription(): void
    {
        $createData = [
            'Text' => '<p>People Wall</p>',
            'TextNoTags' => 'People Wall',
            'UserId' => 3,
            'ItemId' => 3,
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
