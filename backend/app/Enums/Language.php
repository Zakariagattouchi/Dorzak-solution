<?php

namespace App\Enums;

/** Interface languages offered by the frontend (EN + RTL Arabic). */
enum Language: string
{
    case EN = 'en';
    case AR = 'ar';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $l) => $l->value, self::cases());
    }
}
