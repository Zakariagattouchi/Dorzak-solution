<?php

namespace App\Support;

use App\Enums\StaffRole;

/**
 * Single source of truth for role -> ability mapping.
 *
 * Mirrors the ability matrix in docs/backend-planning/06-laravel-implementation-strategy.md §3,
 * which is itself derived from ROLE_CONFIG in the recovered frontend
 * (src/pages/users/UsersPage.tsx). Gates are defined from this map in AuthServiceProvider,
 * and /auth/me returns abilitiesFor() so the SPA can gate its navigation.
 */
final class RoleMatrix
{
    /**
     * ability => roles allowed. Keep in exact sync with docs 06 §3.
     *
     * @var array<string, list<StaffRole>>
     */
    private const MATRIX = [
        'orders.view' => [StaffRole::OWNER, StaffRole::MANAGER, StaffRole::CASHIER, StaffRole::VIEWER],
        'orders.create' => [StaffRole::OWNER, StaffRole::MANAGER, StaffRole::CASHIER],
        'orders.update_status' => [StaffRole::OWNER, StaffRole::MANAGER],

        'products.view' => [StaffRole::OWNER, StaffRole::MANAGER, StaffRole::CASHIER, StaffRole::VIEWER],
        'products.manage' => [StaffRole::OWNER, StaffRole::MANAGER, StaffRole::CASHIER],
        'categories.view' => [StaffRole::OWNER, StaffRole::MANAGER, StaffRole::CASHIER, StaffRole::VIEWER],

        'customers.view' => [StaffRole::OWNER, StaffRole::MANAGER, StaffRole::CASHIER, StaffRole::VIEWER],
        'customers.manage' => [StaffRole::OWNER, StaffRole::MANAGER, StaffRole::CASHIER],
        'customers.delete' => [StaffRole::OWNER, StaffRole::MANAGER],
        'customers.import' => [StaffRole::OWNER, StaffRole::MANAGER],
        'customers.export' => [StaffRole::OWNER, StaffRole::MANAGER],

        'reports.view' => [StaffRole::OWNER, StaffRole::MANAGER, StaffRole::VIEWER],
        'reports.export' => [StaffRole::OWNER, StaffRole::MANAGER],

        'settings.manage' => [StaffRole::OWNER, StaffRole::MANAGER],

        'staff.view' => [StaffRole::OWNER, StaffRole::MANAGER],
        'staff.manage' => [StaffRole::OWNER, StaffRole::MANAGER],

        'billing.manage' => [StaffRole::OWNER],
    ];

    /** @return list<string> every ability key in the matrix */
    public static function abilities(): array
    {
        return array_keys(self::MATRIX);
    }

    /** Whether the given role is granted the given ability. */
    public static function allows(?StaffRole $role, string $ability): bool
    {
        if ($role === null || ! isset(self::MATRIX[$ability])) {
            return false;
        }

        return in_array($role, self::MATRIX[$ability], true);
    }

    /**
     * All abilities granted to a role, in matrix order.
     *
     * @return list<string>
     */
    public static function abilitiesFor(?StaffRole $role): array
    {
        if ($role === null) {
            return [];
        }

        return array_values(array_filter(
            self::abilities(),
            static fn (string $ability): bool => self::allows($role, $ability),
        ));
    }
}
