<?php

namespace App\EDM;

final class LiteralHelper
{
    public static function toNormalizedString(array $literals, string $separator = ' | '): string
    {
        if (empty($literals)) {
            return '';
        }

        $languages = array_unique(
            array_filter(array_map(fn(Literal $lit) => $lit->language, $literals))
        );
        $includeLang = count($languages) > 1;

        $usedValues = [];
        $parts = [];

        foreach ($literals as $i => $lit) {
            $val = trim($lit->value);
            if ($val === '') {
                continue;
            }

            $key = $val . ($includeLang && $lit->language ? "@{$lit->language}" : '');

            // skip duplicates
            if (isset($usedValues[$key])) {
                continue;
            }

            $usedValues[$key] = true;

            $parts[] = $includeLang && $lit->language
                ? "{$val} [{$lit->language}]"
                : $val;
        }

        return implode($separator, $parts);
    }
}
