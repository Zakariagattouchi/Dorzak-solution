<?php

namespace App\Enums;

/**
 * Store currencies offered by the frontend Currency tab (src/pages/settings/SettingsPage.tsx).
 * Symbols mirror that page's option labels and the useMoney() hook's symbol map.
 */
enum Currency: string
{
    case QAR = 'QAR';
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case CAD = 'CAD';
    case BRL = 'BRL';
    case MXN = 'MXN';
    case COP = 'COP';
    case ARS = 'ARS';
    case AUD = 'AUD';

    public function symbol(): string
    {
        return match ($this) {
            self::QAR => 'QAR',
            self::USD, self::MXN, self::COP, self::ARS => '$',
            self::EUR => '€',
            self::GBP => '£',
            self::CAD => 'CA$',
            self::BRL => 'R$',
            self::AUD => 'A$',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
