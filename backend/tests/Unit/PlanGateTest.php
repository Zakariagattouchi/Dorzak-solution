<?php

namespace Tests\Unit;

use App\Enums\PlanFeature;
use App\Exceptions\PlanUpgradeRequiredException;
use App\Models\Plan;
use App\Models\Store;
use App\Services\PlanGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanGateTest extends TestCase
{
    use RefreshDatabase;

    private function gate(): PlanGate
    {
        return app(PlanGate::class);
    }

    public function test_free_plan_denies_boolean_capabilities(): void
    {
        $store = Store::factory()->create();
        $this->assignPlan($store, 'FREE');

        $this->assertFalse($this->gate()->allows($store, PlanFeature::ONLINE_ORDERING));
        $this->assertFalse($this->gate()->allows($store, PlanFeature::BRANDED_STOREFRONT));
    }

    public function test_pro_plan_grants_its_boolean_capabilities(): void
    {
        $store = Store::factory()->create();
        $this->assignPlan($store, 'PRO');

        $this->assertTrue($this->gate()->allows($store, PlanFeature::ONLINE_ORDERING));
        $this->assertTrue($this->gate()->allows($store, PlanFeature::BRANDED_STOREFRONT));
        $this->assertFalse($this->gate()->allows($store, PlanFeature::CUSTOM_DOMAIN));
    }

    public function test_limit_features_are_always_allowed_but_capped(): void
    {
        $store = Store::factory()->create();
        $this->assignPlan($store, 'FREE');

        // Access to "have staff" is always on; the limit caps the count.
        $this->assertTrue($this->gate()->allows($store, PlanFeature::STAFF_SEATS));
        $this->assertSame(1, $this->gate()->limit($store, PlanFeature::STAFF_SEATS));
    }

    public function test_absent_limit_means_unlimited(): void
    {
        $store = Store::factory()->create();
        $this->assignPlan($store, 'ENTERPRISE');

        // Enterprise omits STAFF_SEATS / PRODUCTS_LIMIT rows => unlimited.
        $this->assertNull($this->gate()->limit($store, PlanFeature::STAFF_SEATS));
        $this->assertNull($this->gate()->limit($store, PlanFeature::PRODUCTS_LIMIT));
    }

    public function test_ensure_within_limit_throws_at_the_cap(): void
    {
        $store = Store::factory()->create();
        $this->assignPlan($store, 'FREE'); // STAFF_SEATS = 1

        // One seat already used -> the next would exceed the cap.
        $this->expectException(PlanUpgradeRequiredException::class);
        $this->gate()->ensureWithinLimit($store, PlanFeature::STAFF_SEATS, 1);
    }

    public function test_store_without_subscription_falls_back_to_default_plan(): void
    {
        $store = Store::factory()->create();
        $store->subscription()->delete();
        $store->refresh();

        // Default plan is FREE, so branded storefront stays off.
        $this->assertNull($store->subscription);
        $this->assertSame(Plan::default()->code, 'FREE');
        $this->assertFalse($this->gate()->allows($store, PlanFeature::BRANDED_STOREFRONT));
    }
}
