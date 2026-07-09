<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'CASH';
    case CARD = 'CARD';
    case TRANSFER = 'TRANSFER';
    case WHATSAPP = 'WHATSAPP';
    case FAWRAN = 'FAWRAN';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $m) => $m->value, self::cases());
    }
}
