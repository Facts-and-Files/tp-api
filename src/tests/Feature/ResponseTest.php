<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Str;

class ResponseTest extends TestCase
{
    public function testUnauthorizedNoTokenRequest(): void
    {
        $response = $this->withoutToken()->get('/projects');

        $response->assertStatus(401);
    }

    public function testUnauthorizedWrongTokenRequest(): void
    {
        $randomKey = Str::random($length = 50);

        $response = $this->withToken($randomKey)->get('/projects');

        $response->assertStatus(401);
    }

    public function testAuthorizedRequest(): void
    {
        $response = $this->get('/projects');
        $statusCode = $response->getStatusCode();

        $this->assertNotEquals($statusCode, 401);
    }

    public function testWrongUriRequest(): void
    {
        $response = $this->get('/notfound');

        $response->assertStatus(404);
    }
}
