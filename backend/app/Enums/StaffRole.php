<?php

namespace App\Enums;

/**
 * The four fixed staff roles, sourced verbatim from the reconstructed frontend
 * (src/pages/users/UsersPage.tsx ROLE_CONFIG). See docs 03 (enums) and 06 §3.
 */
enum StaffRole: string
{
    case OWNER = 'OWNER';
    case MANAGER = 'MANAGER';
    case CASHIER = 'CASHIER';
    case VIEWER = 'VIEWER';

    /** Roles that may be assigned via a staff invitation (OWNER is never invitable). */
    public static function assignable(): array
    {
        return [self::MANAGER, self::CASHIER, self::VIEWER];
    }

    public function label(): string
    {
        return ucfirst(strtolower($this->value));
    }
}
