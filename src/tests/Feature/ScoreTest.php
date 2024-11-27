<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Database\Seeders\CampaignDataSeeder;
use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ScoreDataSeeder;
use Database\Seeders\StoryCampaignDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ScoreTest extends TestCase
{
    private static $endpoint = '/scores';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable(): void
    {
        Artisan::call('db:seed', ['--class' => ScoreDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => CampaignDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => StoryCampaignDataSeeder::class]);
    }

    public function testGetAllScores(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => ScoreDataSeeder::$data];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllScoresLimitedAndSorted(): void
    {
        $queryParams = '?limit=1&page=1&orderBy=ScoreId&orderDir=desc';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ScoreDataSeeder::$data[4]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetScoresByUserId(): void
    {
        $queryParams = '?UserId='. ScoreDataSeeder::$data[1]['UserId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ScoreDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetScoresByItemId(): void
    {
        $queryParams = '?ItemId='. ScoreDataSeeder::$data[1]['ItemId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ScoreDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetScoresByStoryId(): void
    {
        $queryParams = '?StoryId='. ItemDataSeeder::$data[1]['StoryId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ScoreDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetScoresByCampaignId(): void
    {
        $queryParams = '?CampaignId='. CampaignDataSeeder::$data[0]['CampaignId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ScoreDataSeeder::$data[0], ScoreDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetScoresByScoreTypeId(): void
    {
        $queryParams = '?ScoreTypeId='. ScoreDataSeeder::$data[1]['ScoreTypeId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ScoreDataSeeder::$data[0], ScoreDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetScoresByFromDatetime(): void
    {
        $queryParams = '?from=' . Carbon::parse(ScoreDataSeeder::$data[1]['Timestamp'] )->sub(1, 'day');
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ScoreDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetScoresByToDatetime(): void
    {
        $queryParams = '?to='. Carbon::parse(ScoreDataSeeder::$data[0]['Timestamp'] )->add(1, 'day');
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ScoreDataSeeder::$data[0]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllScoresByBetweenDatetimes(): void
    {
        $to = Carbon::parse(ScoreDataSeeder::$data[1]['Timestamp'] )->add(1, 'day');
        $from = Carbon::parse(ScoreDataSeeder::$data[0]['Timestamp'] )->sub(1, 'day');
        $queryParams = '?from=' . $from . '&to=' . $to;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ScoreDataSeeder::$data[0], ScoreDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetNoScoreByTimestampBetween(): void
    {
        $to = Carbon::parse(ScoreDataSeeder::$data[1]['Timestamp'] )->sub(1, 'day');
        $from = Carbon::parse(ScoreDataSeeder::$data[0]['Timestamp'] )->add(1, 'day');
        $queryParams = '?from=' . $from . '&to=' . $to;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => []];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testCreateAScore(): void
    {
        // enable this test again if User table and TeamScore table present and populated
        // because it triggers an event
        $this->markTestSkipped('must be revisited.');

        // $createData = [
        //     'ScoreId' => 3,
        //     'ItemId' => 438268,
        //     'UserId' => 3,
        //     'ScoreTypeId' => 3,
        //     'Amount' => 3,
        //     'Timestamp' => '2021-01-03T00:00:00.000000Z'
        // ];
        // $awaitedSuccess = ['success' => true];
        // $awaitedData = ['data' => $createData];
        //
        // $response = $this->post(self::$endpoint, $createData);
        //
        // $response
        //     ->assertOk()
        //     ->assertJson($awaitedSuccess)
        //     ->assertJson($awaitedData);
    }
}
