<?php

namespace Tests;

use App\Enums\StaffRole;
use App\Models\Plan;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Services\PlanGate;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // The array cache store persists across tests in one process; flush it so
        // version-keyed storefront cache entries never leak between tests.
        Cache::flush();
    }

    /**
     * Create a store with an active owner.
     *
     * @return array{store: Store, user: User, membership: StoreUser}
     */
    protected function createStoreWithOwner(array $storeAttributes = [], array $userAttributes = []): array
    {
        $store = Store::factory()->create($storeAttributes);
        $user = User::factory()->create($userAttributes);
        $membership = $this->attachMember($store, $user, StaffRole::OWNER);

        return ['store' => $store, 'user' => $user, 'membership' => $membership];
    }

    /** Create a fresh user as a member of the given store (or a new store) in a role. */
    protected function createMember(StaffRole $role, ?Store $store = null, bool $active = true): User
    {
        $store ??= Store::factory()->create();
        $user = User::factory()->create();
        $this->attachMember($store, $user, $role, $active);

        return $user;
    }

    protected function attachMember(Store $store, User $user, StaffRole $role, bool $active = true): StoreUser
    {
        return StoreUser::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
            'role' => $role,
            'is_active' => $active,
            'joined_at' => now(),
        ]);
    }

    /** Authenticate the given user for Sanctum-guarded requests. */
    protected function actingAsMember(User $user): static
    {
        Sanctum::actingAs($user);

        return $this;
    }

    /** Put a store on a plan by code (FREE|PRO|ENTERPRISE seeded by migration). */
    protected function assignPlan(Store $store, string $code): void
    {
        $store->subscription()->firstOrCreate([])->update([
            'plan_id' => Plan::where('code', $code)->value('id'),
        ]);
        $store->load('subscription.plan.featureLimits');
        app(PlanGate::class)->forget($store);
    }
}
