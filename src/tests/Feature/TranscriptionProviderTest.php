<?php

namespace Tests\Feature;

use Tests\TestCase;

class TranscriptionProviderTest extends TestCase
{
    private static $endpoint = 'transcription-providers';

    private static $data = [
        [
            'TranscriptionProviderId' => 1,
            'Name' => 'ReadCoop-Transkribus',
        ] ,
        [
            'TranscriptionProviderId' => 2,
            'Name' => 'CrossLang-Occam',
        ] ,
    ];

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testGetAllTranscriptionProvider(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$data];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetASingleTranscriptionProvider(): void
    {
        $queryParams = '/'. self::$data[1]['TranscriptionProviderId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$data[1]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testCreateATranscriptionProvider(): void
    {
        $createData = [
            'Name' => 'TestTranscriptionProvider',
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testUpdateATranscriptionProvider(): void
    {
        $updateData = [
           'Name' => 'TestTranscriptionProviderUpdate',
        ];
        $id = self::$data[1]['TranscriptionProviderId'];
        $queryParams = '/' . $id;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $updateData];
        $awaitedData['data']['TranscriptionProviderId'] = $id;

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testUpdateANonExistentTranscriptionProvider(): void
    {
        $queryParams = '/999999';
        $updateData = [];
        $awaitedSuccess = ['success' => false];

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function testDeleteATranscriptionProvider(): void
    {
        $id = self::$data[1]['TranscriptionProviderId'];
        $queryParams = '/' . $id;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$data[1]];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testDeleteANonExistentProject(): void
    {
        $queryParams = '/999999';
        $updateData = [];
        $awaitedSuccess = ['success' => false];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }
}
