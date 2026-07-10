<?php

namespace Tests\Unit;

use App\Models\DeliveryProvider;
use App\Models\Store;
use App\Services\DeliveryQuoteService;
use App\Services\DorzakBusinessClient;
use App\Services\PlanGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryQuoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private const STORE_LAT = 25.2854;

    private const STORE_LNG = 51.531;

    // ~4.4 km north of the store.
    private const PIN_LAT = 25.325;

    private const PIN_LNG = 51.531;

    private function service(): DeliveryQuoteService
    {
        // The network client stays disabled here: these cases cover comparator
        // (local-formula) carriers. Network pricing is covered separately.
        return new DeliveryQuoteService(app(PlanGate::class), app(DorzakBusinessClient::class));
    }

    private function locatedStore(string $plan = 'PRO'): Store
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $store->update(['latitude' => self::STORE_LAT, 'longitude' => self::STORE_LNG]);
        $store->initializeSettings();
        $this->assignPlan($store, $plan);

        return $store->fresh(['storefrontSetting', 'subscription.plan.featureLimits']);
    }

    public function test_no_providers_means_legacy_flat_mode(): void
    {
        $store = $this->locatedStore();
        $store->storefrontSetting->update(['delivery_fee' => 7.5]);
        $store->refresh();

        $q = $this->service()->quote($store, self::PIN_LAT, self::PIN_LNG, 20);

        $this->assertSame('flat', $q['mode']);
        $this->assertSame(7.5, $q['fee']);
    }

    public function test_cheapest_eligible_provider_wins(): void
    {
        $store = $this->locatedStore();
        DeliveryProvider::create(['name' => 'Pricey', 'base_fee' => 10, 'per_km_fee' => 3, 'min_fee' => 0, 'max_radius_km' => 20]);
        DeliveryProvider::create(['name' => 'Cheap', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);

        $q = $this->service()->quote($store, self::PIN_LAT, self::PIN_LNG, 20);

        $this->assertSame('quoted', $q['mode']);
        $this->assertSame('Cheap', $q['provider_name']);
        // fee = 5 + 2 * distance
        $this->assertEqualsWithDelta(5 + 2 * $q['distance_km'], $q['fee'], 0.01);
    }

    public function test_min_fee_clamps_short_trips(): void
    {
        $store = $this->locatedStore();
        DeliveryProvider::create(['name' => 'Clamped', 'base_fee' => 1, 'per_km_fee' => 0.5, 'min_fee' => 9, 'max_radius_km' => 20]);

        $q = $this->service()->quote($store, self::PIN_LAT, self::PIN_LNG, 20);

        $this->assertSame(9.0, $q['fee']);
    }

    public function test_out_of_radius_is_unavailable_without_fallback(): void
    {
        $store = $this->locatedStore();
        DeliveryProvider::create(['name' => 'Tiny radius', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 1]);

        $q = $this->service()->quote($store, self::PIN_LAT, self::PIN_LNG, 20);

        $this->assertSame('unavailable', $q['mode']);
        $this->assertSame('OUT_OF_RANGE', $q['reason']);
    }

    public function test_plan_gated_provider_is_invisible_to_free_plan(): void
    {
        $store = $this->locatedStore('FREE');
        DeliveryProvider::create(['name' => 'Dorzak Delivery', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20, 'is_plan_gated' => true]);
        DeliveryProvider::create(['name' => 'City Courier', 'base_fee' => 10, 'per_km_fee' => 3, 'min_fee' => 0, 'max_radius_km' => 20]);

        $q = $this->service()->quote($store, self::PIN_LAT, self::PIN_LNG, 20);

        // The cheaper Dorzak rate must not leak to a FREE store.
        $this->assertSame('City Courier', $q['provider_name']);
    }

    public function test_plan_gated_provider_unlocks_with_delivery_services(): void
    {
        $store = $this->locatedStore('PRO'); // PRO grants DELIVERY_SERVICES
        DeliveryProvider::create(['name' => 'Dorzak Delivery', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20, 'is_plan_gated' => true]);
        DeliveryProvider::create(['name' => 'City Courier', 'base_fee' => 10, 'per_km_fee' => 3, 'min_fee' => 0, 'max_radius_km' => 20]);

        $q = $this->service()->quote($store, self::PIN_LAT, self::PIN_LNG, 20);

        $this->assertSame('Dorzak Delivery', $q['provider_name']);
    }

    public function test_free_threshold_waives_but_keeps_the_provider(): void
    {
        $store = $this->locatedStore();
        $store->storefrontSetting->update(['free_delivery_threshold' => 50]);
        $store->refresh();
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);

        $q = $this->service()->quote($store, self::PIN_LAT, self::PIN_LNG, 60);

        $this->assertSame('quoted', $q['mode']);
        $this->assertTrue($q['waived']);
        $this->assertSame(0.0, $q['fee']);
        $this->assertSame('Courier', $q['provider_name']);
    }

    public function test_missing_store_location_is_unavailable(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $store->initializeSettings();
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);

        $q = $this->service()->quote($store->fresh('storefrontSetting'), self::PIN_LAT, self::PIN_LNG, 20);

        $this->assertSame('unavailable', $q['mode']);
        $this->assertSame('STORE_LOCATION_MISSING', $q['reason']);
    }

    /**
     * A pickup-less store must never take a delivery order — not even through the
     * manual WhatsApp quote, because no courier could be told where to collect.
     */
    public function test_missing_store_location_is_never_rescued_by_the_whatsapp_fallback(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();
        $store->initializeSettings();
        $store->storefrontSetting->update(['allow_delivery' => true, 'whatsapp_delivery_fallback' => true]);
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);
        $store = $store->fresh('storefrontSetting');

        $q = $this->service()->quote($store, self::PIN_LAT, self::PIN_LNG, 20);

        $this->assertSame('unavailable', $q['mode']);
        $this->assertSame('STORE_LOCATION_MISSING', $q['reason']);
        $this->assertSame('none', $this->service()->storeMode($store));
    }

    public function test_sort_order_breaks_fee_ties(): void
    {
        $store = $this->locatedStore();
        DeliveryProvider::create(['name' => 'B', 'base_fee' => 5, 'per_km_fee' => 0, 'min_fee' => 0, 'max_radius_km' => 20, 'sort_order' => 2]);
        DeliveryProvider::create(['name' => 'A', 'base_fee' => 5, 'per_km_fee' => 0, 'min_fee' => 0, 'max_radius_km' => 20, 'sort_order' => 1]);

        $q = $this->service()->quote($store, self::PIN_LAT, self::PIN_LNG, 20);

        $this->assertSame('A', $q['provider_name']);
    }

    public function test_store_mode_reports_capability_without_a_pin(): void
    {
        $store = $this->locatedStore('FREE');

        // Legacy (no providers): flat pricing still counts as quoted.
        $this->assertSame('quoted', $this->service()->storeMode($store));

        // A plan-gated-only platform leaves a FREE store with nothing.
        DeliveryProvider::create(['name' => 'Dorzak Delivery', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20, 'is_plan_gated' => true]);
        $this->assertSame('none', $this->service()->storeMode($store->fresh('storefrontSetting')));

        // Delivery switched off entirely -> none regardless.
        $store->storefrontSetting->update(['allow_delivery' => false]);
        $this->assertSame('none', $this->service()->storeMode($store->fresh('storefrontSetting')));
    }
}
