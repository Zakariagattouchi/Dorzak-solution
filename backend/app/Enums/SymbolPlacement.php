<?php

namespace App\Enums;

/** Where the currency symbol sits relative to the amount ($100 vs 100 $). */
enum SymbolPlacement: string
{
    case BEFORE = 'BEFORE';
    case AFTER = 'AFTER';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $p) => $p->value, self::cases());
    }
}
