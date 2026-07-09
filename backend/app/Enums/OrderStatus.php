<?php

namespace App\Enums;

enum OrderStatus: string
{
    case CONFIRMING = 'CONFIRMING';
    case ACCEPTED = 'ACCEPTED';
    case PREPARING = 'PREPARING';
    case OUT_FOR_DELIVERY = 'OUT_FOR_DELIVERY';
    case COMPLETE = 'COMPLETE';
    case CANCELLED = 'CANCELLED';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }
}
