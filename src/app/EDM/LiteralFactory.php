<?php

namespace App\EDM;

final class LiteralFactory
{
    public static function fromJsonLd(mixed $input): array
    {
        if ($input === null) {
            return [];
        }

        if (is_string($input)) {
            return [new Literal($input)];
        }

        if (is_array($input) && !array_is_list($input)) {
            return [self::fromObject($input)];
        }

        if (is_array($input) && array_is_list($input)) {
            $literals = [];
            foreach ($input as $v) {
                $literals = array_merge($literals, self::fromJsonLd($v));
            }
            return $literals;
        }

        return [];
    }

    private static function fromObject(array $object): Literal
    {
        if (isset($object['@value'])) {
            $value = (string) $object['@value'];
            $lang = isset($object['@language']) ? (string) $object['@language'] : null;
            $datatype = isset($object['@type']) ? (string) $object['@type'] : null;
            return new Literal($value, $lang, $datatype);
        }

        return new Literal(json_encode($object, JSON_THROW_ON_ERROR));
    }
}
