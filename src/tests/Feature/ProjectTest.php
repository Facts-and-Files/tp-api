<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Database\Seeders\ProjectDataSeeder;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    private static $endpoint = 'projects';

    public function setUp(): void
    {
        parent::setUp();
        self::populateTable();
    }

    public static function populateTable (): void
    {
        Artisan::call('db:seed', ['--class' => ProjectDataSeeder::class]);
    }

    public function testGetAllProjects(): void
    {
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => ProjectDataSeeder::$data];

        $response = $this->get(self::$endpoint);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllProjectsLimitedAndSorted(): void
    {
        $queryParams = '?limit=1&page=1&orderBy=ProjectId&orderDir=desc';
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ProjectDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetAllProjectsByName(): void
    {
        $queryParams = '?Name='. ProjectDataSeeder::$data[1]['Name'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => [ProjectDataSeeder::$data[1]]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testGetASingleProject(): void
    {
        $queryParams = '/'. ProjectDataSeeder::$data[1]['ProjectId'];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => ProjectDataSeeder::$data[1]];

        $response = $this->get(self::$endpoint . $queryParams);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testCreateAProject(): void
    {
        $createData = [
            'ProjectId' => 3,
            'Name'      => 'TestProject',
            'Url'       => 'testproject'
        ];
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $createData];

        $response = $this->post(self::$endpoint, $createData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testUpdateAProject(): void
    {
        $updateData = [
           'Name' => 'UpdatedProject',
           'Url'  => 'updatedproject'
        ];
        $projectId = ProjectDataSeeder::$data[1]['ProjectId'];
        $queryParams = '/' . $projectId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => $updateData];
        $awaitedData['data']['ProjectId'] = $projectId;

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertOk()
            ->assertJson($awaitedSuccess)
            ->assertJson($awaitedData);
    }

    public function testUpdateANonExistentProject(): void
    {
        $queryParams = '/999999';
        $updateData = [];
        $awaitedSuccess = ['success' => false];

        $response = $this->put(self::$endpoint . $queryParams, $updateData);

        $response
            ->assertNotFound()
            ->assertJson($awaitedSuccess);
    }


    public function testDeleteAProject(): void
    {
        $projectId = ProjectDataSeeder::$data[1]['ProjectId'];
        $queryParams = '/' . $projectId;
        $awaitedSuccess = ['success' => true];
        $awaitedData = ['data' => ProjectDataSeeder::$data[1]];

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
