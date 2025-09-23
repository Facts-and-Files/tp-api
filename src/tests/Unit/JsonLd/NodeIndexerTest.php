<?php

namespace Tests\Unit\JsonLd;

use App\Services\JsonLd\NodeIndexer;
use Tests\TestCase;

class NodeIndexerTest extends TestCase
{
    public function test_builds_index_from_nested_nodes(): void
    {
        $data = [
            '@id' => 'root',
            '@type' => 'Thing',
            '@graph' => [
                ['@id' => 'child1', '@type' => 'Entity'],
                ['@id' => 'child2', '@type' => 'Entity'],
            ],
        ];

        $indexer = new NodeIndexer();
        $indexer->build($data);

        $this->assertTrue($indexer->has('root'));
        $this->assertTrue($indexer->has('child1'));
        $this->assertTrue($indexer->has('child2'));
        $this->assertCount(3, $indexer->all());
    }
}
