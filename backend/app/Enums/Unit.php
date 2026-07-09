<?php

namespace App\Enums;

/** Product units seen in the recovered seed data + the create form. */
enum Unit: string
{
    case PCS = 'pcs';
    case KG = 'kg';
    case G = 'g';
    case L = 'l';
    case ML = 'ml';
    case BOX = 'box';
    case M = 'm';
    case BOTTLE = 'bottle';
    case OTHER = 'other';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $u) => $u->value, self::cases());
    }
}
