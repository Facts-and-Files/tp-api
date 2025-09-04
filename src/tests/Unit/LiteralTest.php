<?php

namespace Tests\Unit;

use App\EDM\LiteralFactory;
use Tests\TestCase;

final class LiteralTest extends TestCase
{
    public function test_language_literal(): void
    {
        $input = ['@value' => 'Hallo', '@language' => 'de'];
        $literals = LiteralFactory::fromJsonLd($input);

        $this->assertCount(1, $literals);
        $this->assertSame('Hallo', $literals[0]->value);
        $this->assertSame('de', $literals[0]->language);
    }
}
