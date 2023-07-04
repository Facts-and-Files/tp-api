<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Feature\ProjectTest;

class DatasetTest extends TestCase
{
    private static $endpoint = '/datasets';

    private static $tableName = 'Dataset';

    private static $tableData = [
        [
            'DatasetId' => 1,
            'Name'      => 'Dataset-1',
            'ProjectId' => 1
        ],
        [
            'DatasetId' => 2,
            'Name'      => 'Dataset-2',
            'ProjectId' => 2
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        ProjectTest::populateTable();
        self::populateTable();

    }

    public static function populateTable(): void
    {
        DB::table(self::$tableName)->insert(self::$tableData);
    }

    public function testGetAllDatasets(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllDatasetsLimitedAndSorted(): void
    {
        $queryParams = '?limit=1&page=1&orderBy=DatasetId&orderDir=desc';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllDatasetsByProjectId(): void
    {
        $queryParams = '?ProjectId='. self::$tableData[1]['ProjectId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllDatasetsByName(): void
    {
        $queryParams = '?Name='. self::$tableData[1]['Name'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [self::$tableData[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testAGetSingleDataset(): void
    {
        $queryParams = '/'. self::$tableData[1]['DatasetId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData[1]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetANonExistentSingleDataset(): void
    {
        $queryParams = '/999999';
        $awaitedSuccess = ['success' => false];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function testCreateADataset(): void
    {
        $createData = [
            'Name'      => 'TestDataset',
            'ProjectId' => 2
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];
        $awaitedData['data']['DatasetId'] = 3;

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testUpdateADataset(): void
    {
        $updateData = [
           'Name'      => 'UpdatedDataset',
           'ProjectId' => 1
        ];
        $datasetId = self::$tableData[1]['DatasetId'];
        $queryParams = '/' . $datasetId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $updateData];
        $awaitedData['data']['DatasetId'] = $datasetId;

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testUpdateANonExistentDataset(): void
    {
        $queryParams = '/999999';
        $updateData = [];
        $awaitedSuccess = ['success' => false];

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertStatus(400)
            ->assertJson($awaitedSuccess);
    }

    public function testDeleteADataset(): void
    {
        $datasetId = self::$tableData[1]['DatasetId'];
        $queryParams = '/' . $datasetId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => self::$tableData[1]];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testDeleteANonExistentDataset(): void
    {
        $queryParams = '/999999';
        $updateData = [];
        $awaitedSuccess = ['success' => false];

        $response = $this->delete(self::$endpoint . $queryParams);

        $response
            ->assertStatus(400)
            ->assertJson($awaitedSuccess);
    }

}
