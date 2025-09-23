<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Feature\DatasetTest;
use Tests\Feature\ProjectTest;
use Illuminate\Support\Facades\Storage;

class DeiImportTest extends TestCase
{
    private static $endpoint = 'import/dei/projects';

    private static $projectId = 1;

    private static $importData = [];

    public function setUp(): void
    {
        parent::setUp();
        ProjectTest::populateTable();
        DatasetTest::populateTable();
        self::$importData = json_decode(
            file_get_contents(__DIR__ . '/data/edm-complete.json'),
            true,
        );
    }

    public function test_import_with_non_existent_project_returns_404(): void
    {
        $queryParams = '/1000000';
        $awaitedSuccess = ['success' => false];

        $response = $this->post(self::$endpoint . $queryParams, self::$importData);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }

    public function test_import_with_missing_importname_id_returns_422(): void
    {
        $queryParams = '/' . self::$projectId;
        $awaitedSuccess = ['success' => false];

        $response = $this->post(self::$endpoint . $queryParams, self::$importData);

        $response
            ->assertStatus(422)
            ->assertJson($awaitedSuccess);
    }

    public function test_import_with_missing_json_graph_returns_422(): void
    {
        $queryParams = '/' . self::$projectId . '?ImportName=Test';
        $awaitedSuccess = ['success' => false];

        $response = $this->post(self::$endpoint . $queryParams, []);

        $response
            ->assertStatus(422)
            ->assertJson($awaitedSuccess);
    }

    public function test_import_with_non_existent_dataset_returns_422(): void
    {
        $queryParams = '/' . self::$projectId . '?DatasetId=1000&ImportName=Test';
        $awaitedSuccess = ['success' => false];

        $response = $this->post(self::$endpoint . $queryParams, self::$importData);

        $response
            ->assertStatus(422)
            ->assertJson($awaitedSuccess);
    }

    public function test_import_stores_the_body_as_file(): void
    {

        $this->markTestSkipped('must be revisited.');

        Storage::fake('imports');
        $queryParams = '/' . self::$projectId . '?ImportName=Test';
        $awaitedSuccess = ['success' => true];

        $response = $this->post(self::$endpoint . $queryParams, self::$importData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess);

        Storage::disk('imports')->assertExists('folder/' . $file->hashName());
    }

    public function test_import(): void
    {
        $queryParams = '/' . self::$projectId . '?ImportName=Test';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' =>
            [
            ]
        ];

        $response = $this->post(self::$endpoint . $queryParams, self::$importData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }
}
