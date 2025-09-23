<?php

namespace Tests\Unit\JsonLd;

use App\Services\JsonLd\LiteralResolver;
use App\EDM\Literal;
use Tests\TestCase;

class LiteralResolverTest extends TestCase
{
    private LiteralResolver $resolver;
    private array $emptyIndex = [];

    protected function setUp(): void
    {
        $this->resolver = new LiteralResolver();
    }

    public function test_resolves_string_literal(): void
    {
        $literals = $this->resolver->resolve('Hello', $this->emptyIndex);

        $this->assertCount(1, $literals);
        $this->assertInstanceOf(Literal::class, $literals[0]);
        $this->assertSame('Hello', $literals[0]->value);
    }

    public function test_resolves_reference_to_entity(): void
    {
        $entity = ['@id' => 'e1', '@type' => 'Person', 'skos:prefLabel' => 'Rembrandt'];
        $index  = ['e1' => $entity];

        $value = ['@id' => 'e1'];

        $literals = $this->resolver->resolve($value, $index);

        $this->assertCount(1, $literals);
        $this->assertSame('Rembrandt', $literals[0]->value);
    }
}
