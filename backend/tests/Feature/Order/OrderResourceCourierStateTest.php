<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * OrderResource courier state fields (Issue 24).
 *
 * `courier_state`, `delivery_dispatched_at`, `delivery_external_reference`
 * are only populated for Dorzak network-dispatched orders (delivery_provider_code = 'dorzak').
 * Non-network orders always get null for all three fields.
 */
class OrderResourceCourierStateTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function dorzakOrder(array $attrs = []): Order
    {
        return Order::factory()->create(array_merge([
            'store_id' => $this->store->id,
            'fulfillment' => 'DELIVERY',
            'status' => OrderStatus::OUT_FOR_DELIVERY,
            'delivery_provider_code' => 'dorzak',
            'delivery_external_reference' => 'TND-1234',
            'delivery_external_status' => 'en_route_customer',
            'delivery_dispatched_at' => now()->subMinutes(10),
        ], $attrs));
    }

    private function nonNetworkOrder(array $attrs = []): Order
    {
        return Order::factory()->create(array_merge([
            'store_id' => $this->store->id,
            'fulfillment' => 'DELIVERY',
            'status' => OrderStatus::OUT_FOR_DELIVERY,
            'delivery_provider_code' => 'uber',
        ], $attrs));
    }

    // -------------------------------------------------------------------------
    // courier_state label mappings
    // -------------------------------------------------------------------------

    #[DataProvider('courierLabelProvider')]
    public function test_courier_state_maps_raw_status_to_human_label(string $raw, string $expected): void
    {
        $order = $this->dorzakOrder(['delivery_external_status' => $raw]);

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.courier_state', $expected);
    }

    /** @return array<string, array{string, string}> */
    public static function courierLabelProvider(): array
    {
        return [
            'pending_dispatch'  => ['pending_dispatch',  'Finding a driver'],
            'auctioning'        => ['auctioning',        'Finding a driver'],
            'en_route_pickup'   => ['en_route_pickup',   'Driver en route to you'],
            'en_route_customer' => ['en_route_customer', 'Out for delivery'],
            'out_for_delivery'  => ['out_for_delivery',  'Out for delivery'],
            'delivered'         => ['delivered',         'Delivered'],
            'failed'            => ['failed',            'Delivery failed'],
            'returned'          => ['returned',          'Delivery returned'],
        ];
    }

    public function test_unknown_raw_status_is_humanised_as_fallback(): void
    {
        $order = $this->dorzakOrder(['delivery_external_status' => 'some_unknown_state']);

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.courier_state', 'Some Unknown State');
    }

    public function test_courier_state_is_null_when_no_external_status(): void
    {
        $order = $this->dorzakOrder(['delivery_external_status' => null]);

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.courier_state', null);
    }

    // -------------------------------------------------------------------------
    // Non-network orders return null for all courier fields
    // -------------------------------------------------------------------------

    public function test_non_network_order_has_null_courier_fields(): void
    {
        $order = $this->nonNetworkOrder([
            'delivery_external_status' => 'en_route_customer',
            'delivery_external_reference' => 'UBER-99',
            'delivery_dispatched_at' => now()->subMinutes(5),
        ]);

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.courier_state', null)
            ->assertJsonPath('data.delivery_dispatched_at', null)
            ->assertJsonPath('data.delivery_external_reference', null);
    }

    public function test_pickup_order_has_null_courier_fields(): void
    {
        $order = Order::factory()->create([
            'store_id' => $this->store->id,
            'fulfillment' => 'PICKUP',
            'status' => OrderStatus::PREPARING,
        ]);

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.courier_state', null)
            ->assertJsonPath('data.delivery_dispatched_at', null)
            ->assertJsonPath('data.delivery_external_reference', null);
    }

    // -------------------------------------------------------------------------
    // Dorzak orders expose all three fields
    // -------------------------------------------------------------------------

    public function test_dorzak_order_exposes_dispatched_at_and_reference(): void
    {
        $dispatchedAt = now()->subMinutes(8);
        $order = $this->dorzakOrder([
            'delivery_external_reference' => 'TND-9001',
            'delivery_dispatched_at' => $dispatchedAt,
            'delivery_external_status' => 'en_route_pickup',
        ]);

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.courier_state', 'Driver en route to you')
            ->assertJsonPath('data.delivery_external_reference', 'TND-9001')
            ->assertJsonFragment(['delivery_dispatched_at' => $dispatchedAt->toIso8601String()]);
    }

    // -------------------------------------------------------------------------
    // List endpoint also includes courier fields
    // -------------------------------------------------------------------------

    public function test_order_list_includes_courier_state(): void
    {
        $this->dorzakOrder(['delivery_external_status' => 'delivered', 'delivery_external_reference' => 'TND-1234']);
        $this->nonNetworkOrder(['delivery_external_status' => 'delivered']);

        $response = $this->actingAsMember($this->owner)
            ->getJson('/api/v1/orders')
            ->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);

        // The Dorzak row has its external reference exposed; the Uber row does not.
        $dorzakRow = collect($data)->first(fn ($o) => $o['delivery_external_reference'] === 'TND-1234');
        $uberRow   = collect($data)->first(fn ($o) => $o['delivery_external_reference'] === null);

        $this->assertNotNull($dorzakRow, 'Dorzak row must be found by external reference');
        $this->assertNotNull($uberRow, 'Uber row must be found with null external reference');

        $this->assertSame('Delivered', $dorzakRow['courier_state']);
        $this->assertNull($uberRow['courier_state']);
    }
}
