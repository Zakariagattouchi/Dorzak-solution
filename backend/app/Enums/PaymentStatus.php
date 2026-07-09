<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'UNPAID';
    case PENDING_VERIFICATION = 'PENDING_VERIFICATION';
    case PAID = 'PAID';
    case FAILED = 'FAILED';
    case REFUNDED = 'REFUNDED';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }
}
