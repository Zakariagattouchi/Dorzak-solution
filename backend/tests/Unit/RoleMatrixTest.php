<?php

namespace Tests\Unit;

use App\Enums\StaffRole;
use App\Support\RoleMatrix;
use PHPUnit\Framework\TestCase;

/**
 * Pins the ability matrix to docs/backend-planning/06-laravel-implementation-strategy.md §3.
 * If a role's permissions change, this test must change with the doc.
 */
class RoleMatrixTest extends TestCase
{
    /**
     * The full expected matrix, ability => roles allowed.
     *
     * @return array<string, list<StaffRole>>
     */
    private function expectedMatrix(): array
    {
        return [
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
    }

    public function test_matrix_matches_spec(): void
    {
        $expected = $this->expectedMatrix();

        $this->assertSame(
            array_keys($expected),
            RoleMatrix::abilities(),
            'Ability list drifted from the spec.'
        );

        foreach ($expected as $ability => $allowedRoles) {
            foreach (StaffRole::cases() as $role) {
                $shouldAllow = in_array($role, $allowedRoles, true);
                $this->assertSame(
                    $shouldAllow,
                    RoleMatrix::allows($role, $ability),
                    "Mismatch for {$role->value} / {$ability}"
                );
            }
        }
    }

    public function test_owner_has_every_ability(): void
    {
        $this->assertSame(RoleMatrix::abilities(), RoleMatrix::abilitiesFor(StaffRole::OWNER));
    }

    public function test_viewer_is_read_only(): void
    {
        $viewer = RoleMatrix::abilitiesFor(StaffRole::VIEWER);

        $this->assertContains('orders.view', $viewer);
        $this->assertContains('reports.view', $viewer);
        $this->assertNotContains('orders.create', $viewer);
        $this->assertNotContains('products.manage', $viewer);
        $this->assertNotContains('settings.manage', $viewer);
    }

    public function test_cashier_can_manage_products_but_not_settings_or_reports(): void
    {
        $this->assertTrue(RoleMatrix::allows(StaffRole::CASHIER, 'products.manage'));
        $this->assertTrue(RoleMatrix::allows(StaffRole::CASHIER, 'orders.create'));
        $this->assertFalse(RoleMatrix::allows(StaffRole::CASHIER, 'settings.manage'));
        $this->assertFalse(RoleMatrix::allows(StaffRole::CASHIER, 'reports.view'));
    }

    public function test_only_owner_manages_billing(): void
    {
        $this->assertTrue(RoleMatrix::allows(StaffRole::OWNER, 'billing.manage'));
        $this->assertFalse(RoleMatrix::allows(StaffRole::MANAGER, 'billing.manage'));
    }

    public function test_null_role_and_unknown_ability_are_denied(): void
    {
        $this->assertFalse(RoleMatrix::allows(null, 'orders.view'));
        $this->assertFalse(RoleMatrix::allows(StaffRole::OWNER, 'does.not.exist'));
        $this->assertSame([], RoleMatrix::abilitiesFor(null));
    }
}
