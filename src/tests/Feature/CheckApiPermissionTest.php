<?php

namespace Tests\Feature;

use Database\Seeders\ProjectDataSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckApiPermissionTest extends TestCase
{
    private static string $endpoint = '/projects';

    private string $roToken;
    private string $granularToken;
    private string $resourceReadToken;
    private string $resourceWriteToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => ProjectDataSeeder::class]);

        $this->roToken = $this->createToken(['read']);
        $this->granularToken = $this->createToken(['read', 'persons:write']);
        $this->resourceReadToken = $this->createToken(['projects:read']);
        $this->resourceWriteToken = $this->createToken(['projects:write']);
    }

    public function test_request_without_token_is_rejected(): void
    {
        // override the base TestCase token by making a raw request without withToken()
        $response = $this->get(self::$endpoint, ['Authorization' => '']);

        $response->assertUnauthorized();
    }

    public function test_request_with_invalid_token_is_rejected(): void
    {
        $response = $this->withToken('not-a-valid-token')->get(self::$endpoint);

        $response->assertUnauthorized();
    }

    public function test_rw_token_allows_get(): void
    {
        $response = $this->get(self::$endpoint);

        $response->assertOk();
    }

    public function test_rw_token_allows_post(): void
    {
        $createData = [
            'ProjectId' => 1000,
            'Name' => 'TestName',
            'Url' => 'TestUrl',
        ];

        $response = $this->post(self::$endpoint, $createData);

        $response->assertOk();
    }

    public function test_rw_token_allows_put(): void
    {
        $projectId = ProjectDataSeeder::$data[0]['ProjectId'];

        $response = $this->put(self::$endpoint . '/' . $projectId, ['Name' => 'Updated']);

        $response->assertOk();
    }

    public function test_rw_token_allows_delete(): void
    {
        $projectId = ProjectDataSeeder::$data[0]['ProjectId'];

        $response = $this->delete(self::$endpoint . '/' . $projectId);

        $response->assertOk();
    }

    public function test_ro_token_allows_get(): void
    {
        $response = $this->withToken($this->roToken)->get(self::$endpoint);

        $response->assertOk();
    }

    public function test_ro_token_denies_post(): void
    {
        $createData = [
            'ProjectId' => 1000,
            'Name' => 'TestName',
            'Url' => 'TestUrl',
        ];

        $response = $this->withToken($this->roToken)->post(self::$endpoint, $createData);

        $response->assertForbidden();
    }

    public function test_ro_token_denies_put(): void
    {
        $projectId = ProjectDataSeeder::$data[0]['ProjectId'];

        $response = $this->withToken($this->roToken)
            ->put(self::$endpoint . '/' . $projectId, ['Name' => 'Updated']);

        $response->assertForbidden();
    }

    public function test_ro_token_denies_delete(): void
    {
        $projectId = ProjectDataSeeder::$data[0]['ProjectId'];

        $response = $this->withToken($this->roToken)
            ->delete(self::$endpoint . '/' . $projectId);

        $response->assertForbidden();
    }

    public function test_granular_token_allows_get_on_any_route(): void
    {
        $response = $this->withToken($this->granularToken)->get(self::$endpoint);

        $response->assertOk();
    }

    public function test_granular_token_allows_post_on_granted_resource(): void
    {
        $response = $this->withToken($this->granularToken)->post('/persons', []);

        // Middleware passed — only asserting it's not a 403
        $this->assertNotEquals(403, $response->status());
    }

    public function test_granular_token_denies_post_on_non_granted_resource(): void
    {
        $createData = [
            'ProjectId' => 1000,
            'Name' => 'TestName',
            'Url' => 'TestUrl',
        ];

        $response = $this->withToken($this->granularToken)->post(self::$endpoint, $createData);

        $response->assertForbidden();
    }

    public function test_granular_token_denies_put_on_non_granted_resource(): void
    {
        $projectId = ProjectDataSeeder::$data[0]['ProjectId'];

        $response = $this->withToken($this->granularToken)
            ->put(self::$endpoint . '/' . $projectId, ['Name' => 'Updated']);

        $response->assertForbidden();
    }

    public function test_resource_read_token_allows_get_on_granted_resource(): void
    {
        $response = $this->withToken($this->resourceReadToken)->get(self::$endpoint);

        $response->assertOk();
    }

    public function test_resource_read_token_denies_get_on_other_resource(): void
    {
        $response = $this->withToken($this->resourceReadToken)->get('/persons');

        $response->assertForbidden();
    }

    public function testResourceReadTokenDeniesPost(): void
    {
        $createData = [
            'ProjectId' => 1000,
            'Name' => 'TestName',
            'Url' => 'TestUrl',
        ];

        $response = $this->withToken($this->resourceReadToken)->post(self::$endpoint, $createData);

        $response->assertForbidden();
    }

    public function test_resource_write_token_allows_post_on_granted_resource(): void
    {
        $createData = [
            'ProjectId' => 1000,
            'Name' => 'TestName',
            'Url' => 'TestUrl',
        ];

        $response = $this->withToken($this->resourceWriteToken)->post(self::$endpoint, $createData);

        // middleware passed — only asserting it's not a 403
        $this->assertNotEquals(403, $response->status());
    }

    public function test_resource_write_token_denies_post_on_other_resource(): void
    {
        $response = $this->withToken($this->resourceWriteToken)->post('/persons', []);

        $response->assertForbidden();
    }

    public function test_resource_write_token_denies_get(): void
    {
        // projects:write alone grants no read access
        $response = $this->withToken($this->resourceWriteToken)->get(self::$endpoint);

        $response->assertForbidden();
    }

    public function test_token_description_is_stored(): void
    {
        $plain = Str::random(60);

        DB::table('api_clients')->insert([
            'api_token'   => hash('sha256', $plain),
            'permissions' => json_encode(['*']),
            'description' => 'Test token for CI',
        ]);

        $row = DB::table('api_clients')
            ->where('api_token', hash('sha256', $plain))
            ->first();

        $this->assertSame('Test token for CI', $row->description);
    }

    private function createToken(array $permissions): string
    {
        $plain = Str::random(60);

        DB::table('api_clients')->insert([
            'api_token'   => hash('sha256', $plain),
            'permissions' => json_encode($permissions),
        ]);

        return $plain;
    }
}
