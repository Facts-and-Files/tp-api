<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationTest extends TestCase
{
    public function test_request_to_openapi_yaml_returns_200(): void
    {
        $response = $this->get('/documentation/api-docs.yaml');

        $response->assertStatus(200);
    }
}
