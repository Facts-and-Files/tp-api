<?php

namespace Tests\Feature;

use Tests\TestCase;

class CampaignStatsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testGetNotFoundOnNonExistentCampaign(): void
    {
        $this->markTestSkipped('must be revisited.');
        // $camaignId = 1;
        // $endpoint = '/campaigns/' . $camaignId . '/statistics';
        // $queryParams = '?limit=1&page=1&orderBy=ScoreId&orderDir=desc';
        // $awaitedSuccess = ['success' => false];
        //
        // $response = $this->get($endpoint . $queryParams);
        //
        // $response
        //     ->assertNotFound()
        //     ->assertJson($awaitedSuccess);
    }
}
