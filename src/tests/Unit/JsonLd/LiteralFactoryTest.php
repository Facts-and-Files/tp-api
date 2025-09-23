<?php

namespace Tests\Unit\JsonLd;

use App\EDM\Literal;
use App\EDM\LiteralFactory;
use Tests\TestCase;

final class LiteralFactoryTest extends TestCase
{
    public function test_plain_string(): void
    {
        $input = 'Hello';
        $literals = LiteralFactory::fromJsonLd($input);

        $this->assertCount(1, $literals);
        $this->assertInstanceOf(Literal::class, $literals[0]);
        $this->assertSame('Hello', $literals[0]->value);
        $this->assertNull($literals[0]->language);
        $this->assertNull($literals[0]->datatype);
    }

    public function test_typed_literal(): void
    {
        $input = ['@value' => '123', '@type' => 'xsd:integer'];
        $literals = LiteralFactory::fromJsonLd($input);

        $this->assertCount(1, $literals);
        $this->assertSame('123', $literals[0]->value);
        $this->assertNull($literals[0]->language);
        $this->assertSame('xsd:integer', $literals[0]->datatype);
    }

    public function test_language_tagged_literal(): void
    {
        $input = ['@value' => 'Hallo', '@language' => 'de'];
        $literals = LiteralFactory::fromJsonLd($input);

        $this->assertCount(1, $literals);
        $this->assertSame('Hallo', $literals[0]->value);
        $this->assertSame('de', $literals[0]->language);
        $this->assertNull($literals[0]->datatype);
    }

    public function test_array_of_literals(): void
    {
        $input = [
            'Hello',
            ['@value' => 'Bonjour', '@language' => 'fr'],
            ['@value' => '123', '@type' => 'xsd:integer'],
        ];
        $literals = LiteralFactory::fromJsonLd($input);

        $this->assertCount(3, $literals);

        $this->assertSame('Hello', $literals[0]->value);

        $this->assertSame('Bonjour', $literals[1]->value);
        $this->assertSame('fr', $literals[1]->language);

        $this->assertSame('123', $literals[2]->value);
        $this->assertSame('xsd:integer', $literals[2]->datatype);
    }

    public function test_object_without_value(): void
    {
        $input = ['unexpected' => 'structure'];
        $literals = LiteralFactory::fromJsonLd($input);

        $this->assertCount(1, $literals);

        // should have JSON-encoded fallback
        $this->assertJson($literals[0]->value);
        $this->assertStringContainsString('unexpected', $literals[0]->value);
    }
}
