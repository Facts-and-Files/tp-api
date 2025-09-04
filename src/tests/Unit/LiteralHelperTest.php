<?php

namespace Tests\Unit;

use App\EDM\Literal;
use App\EDM\LiteralHelper;
use Tests\TestCase;

class LiteralHelperTest extends TestCase
{
    public function test_normalized_string_simple(): void
    {
        $literals = [
            new Literal('Starry Night'),
            new Literal('Der Schrei'),
            new Literal('Starry Night'), // duplicate
            new Literal('Skrik')
        ];

        $result = LiteralHelper::toNormalizedString($literals);

        $this->assertSame('Starry Night | Der Schrei | Skrik', $result);
    }

    public function test_normalized_string_with_languages(): void
    {
        $literals = [
            new Literal('The Scream', 'en'),
            new Literal('Der Schrei', 'de'),
            new Literal('Skrik'), // no language
            new Literal('Der Schrei', 'de') // duplicate with same language
        ];

        $result = LiteralHelper::toNormalizedString($literals);

        $this->assertSame('The Scream [en] | Der Schrei [de] | Skrik', $result);
    }

    public function test_normalized_string_single_language(): void
    {
        $literals = [
            new Literal('Les Demoiselles d’Avignon', 'fr'),
            new Literal('Les Demoiselles d’Avignon', 'fr'), // duplicate
        ];

        $result = LiteralHelper::toNormalizedString($literals);

        $this->assertSame('Les Demoiselles d’Avignon', $result);
    }

    public function test_normalized_string_empty_array(): void
    {
        $literals = [];

        $result = LiteralHelper::toNormalizedString($literals);

        $this->assertSame('', $result);
    }
}
