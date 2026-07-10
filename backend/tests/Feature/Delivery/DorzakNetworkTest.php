<?php

namespace Tests\Feature\Delivery;

use App\Enums\OrderStatus;
use App\Jobs\CancelDorzakDelivery;
use App\Jobs\DispatchDorzakDelivery;
use App\Models\DeliveryProvider;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Services\DeliveryQuoteService;
use App\Services\DorzakBusinessClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The Dorzak network carrier: priced live by the delivery system (which quotes
 * 20% under the comparators we send it), and the only carrier we dispatch to
 * the driver board.
 */
class DorzakNetworkTest extends TestCase
{
    use RefreshDatabase;

    private const STORE_LAT = 25.2854;

    private const STORE_LNG = 51.531;

    private const PIN_LAT = 25.325;   // ~4.4 km north

    private const PIN_LNG = 51.531;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('delivery.business', [
            'enabled' => true,
            'base_url' => 'https://delivery.test',
            'client_id' => 'merchant',
            'client_secret' => 'shhh',
            'webhook_secret' => 'hook',
            'timeout' => 8,
            'source_system' => 'dorzak-merchant',
        ]);

        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
        $this->store->update(['latitude' => self::STORE_LAT, 'longitude' => self::STORE_LNG]);
        $this->store->initializeSettings();
        $this->assignPlan($this->store, 'PRO');
        $this->store = $this->store->fresh(['storefrontSetting', 'subscription.plan.featureLimits']);
    }

    private function seedCarriers(): void
    {
        DeliveryProvider::create(['name' => 'Dorzak Delivery', 'code' => 'dorzak', 'kind' => 'network', 'base_fee' => 0, 'per_km_fee' => 0, 'min_fee' => 0, 'max_radius_km' => 20, 'is_plan_gated' => true]);
        // Comparators: Uber = max(0, 10 + 3×4.4) = 23.2, Snoonu = 20 + 1×4.4 = 24.4
        DeliveryProvider::create(['name' => 'Uber', 'code' => 'uber', 'base_fee' => 10, 'per_km_fee' => 3, 'min_fee' => 0, 'max_radius_km' => 20]);
        DeliveryProvider::create(['name' => 'Snoonu', 'code' => 'snoonu', 'base_fee' => 20, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 20]);
    }

    /** @param array<string, mixed> $quote */
    private function fakeNetwork(array $quote = [], array $delivery = []): void
    {
        Http::fake([
            'delivery.test/api/integration/oauth/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            'delivery.test/api/integration/quotes' => Http::response(array_merge([
                'quote_id' => 77,
                'customer_price_minor' => 1856, // 20% under Uber's 23.20
                'currency' => 'QAR',
                'expires_at' => now()->addMinutes(10)->toIso8601String(),
            ], $quote)),
            'delivery.test/api/integration/delivery-requests*' => Http::response(array_merge([
                'tender_id' => 'TND-9001',
                'status' => 'auctioning',
                'closes_at' => now()->addMinutes(5)->toIso8601String(),
            ], $delivery)),
        ]);
    }

    private function quote(): array
    {
        return app(DeliveryQuoteService::class)
            ->quote($this->store, self::PIN_LAT, self::PIN_LNG, 20);
    }

    public function test_the_network_price_wins_and_the_comparators_are_sent_to_it(): void
    {
        $this->seedCarriers();
        $this->fakeNetwork();

        $q = $this->quote();

        $this->assertSame('quoted', $q['mode']);
        $this->assertSame('Dorzak Delivery', $q['provider_name']);
        $this->assertSame('dorzak', $q['provider_code']);
        $this->assertSame(18.56, $q['fee']);
        $this->assertSame(77, $q['quote_id']);
        $this->assertCount(3, $q['options']);

        // We never price Dorzak ourselves — we hand the network the comparator
        // prices it has to undercut.
        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/api/integration/quotes')) {
                return false;
            }

            $sent = collect($request['comparator_quotes'])->keyBy('provider');

            return $sent->count() === 2
                && $sent['uber']['amount_minor'] === 2320
                && $sent['snoonu']['amount_minor'] === 2440
                && $request['pickup_latitude'] === self::STORE_LAT
                && $request['dropoff_latitude'] === self::PIN_LAT;
        });
    }

    public function test_an_unreachable_network_hides_dorzak_instead_of_guessing_a_price(): void
    {
        $this->seedCarriers();
        Http::fake([
            'delivery.test/api/integration/oauth/token' => Http::response(['access_token' => 'tok']),
            'delivery.test/api/integration/quotes' => Http::response(['message' => 'boom'], 500),
        ]);

        $q = $this->quote();

        $this->assertSame('quoted', $q['mode']);
        $this->assertSame('Uber', $q['provider_name']); // cheapest comparator
        $this->assertNull($q['quote_id']);
        $this->assertCount(2, $q['options']);
    }

    public function test_a_free_plan_never_sees_the_network_carrier(): void
    {
        $this->seedCarriers();
        $this->fakeNetwork();
        $this->assignPlan($this->store, 'FREE');
        $this->store = $this->store->fresh(['storefrontSetting', 'subscription.plan.featureLimits']);

        $q = $this->quote();

        $this->assertSame('Uber', $q['provider_name']);
        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/api/integration/quotes'));
    }

    private function acceptedOrder(array $attrs = []): Order
    {
        return Order::factory()->create(array_merge([
            'store_id' => $this->store->id,
            'fulfillment' => 'DELIVERY',
            'status' => OrderStatus::ACCEPTED,
            'payment_method' => 'CASH',
            'payment_status' => 'UNPAID',
            'delivery_provider_code' => 'dorzak',
            'delivery_quote_id' => 77,
            'pickup_latitude' => self::STORE_LAT,
            'pickup_longitude' => self::STORE_LNG,
            'subtotal' => 40,
            'delivery_fee' => 18.56,
            'total' => 58.56,
        ], $attrs));
    }

    public function test_accepting_the_order_opens_the_driver_auction(): void
    {
        $this->fakeNetwork();
        $order = $this->acceptedOrder();

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'PREPARING'])
            ->assertOk();

        $order->refresh();
        $this->assertSame('TND-9001', $order->delivery_external_reference);
        $this->assertSame('auctioning', $order->delivery_external_status);
        $this->assertNotNull($order->delivery_dispatched_at);

        // Cash order => the driver is told what to collect.
        Http::assertSent(fn ($r) => str_contains($r->url(), '/delivery-requests')
            && $r['quote_id'] === 77
            && $r['source_order_id'] === (string) $order->id
            && $r['cod_amount_minor'] === 5856
            && $r->hasHeader('Idempotency-Key'));
    }

    public function test_dispatch_is_skipped_for_comparator_carriers_and_repeat_runs(): void
    {
        $this->fakeNetwork();

        // A customer who picked Uber is never sent to our driver board.
        $uberOrder = $this->acceptedOrder(['delivery_provider_code' => 'uber', 'delivery_quote_id' => null, 'status' => OrderStatus::PREPARING]);
        (new DispatchDorzakDelivery($uberOrder->id))->handle(app(DorzakBusinessClient::class));

        // A retry after a successful dispatch must not double-book a driver.
        $dispatched = $this->acceptedOrder(['status' => OrderStatus::PREPARING, 'delivery_external_reference' => 'TND-1']);
        (new DispatchDorzakDelivery($dispatched->id))->handle(app(DorzakBusinessClient::class));

        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/delivery-requests'));
        $this->assertSame('TND-1', $dispatched->fresh()->delivery_external_reference);
    }

    public function test_cancelling_a_dispatched_order_stands_the_driver_down(): void
    {
        Http::fake([
            'delivery.test/api/integration/oauth/token' => Http::response(['access_token' => 'tok']),
            'delivery.test/*/cancel' => Http::response(['status' => 'cancelled']),
        ]);
        $order = $this->acceptedOrder(['status' => OrderStatus::PREPARING, 'delivery_external_reference' => 'TND-9001']);

        (new CancelDorzakDelivery($order->id))->handle(app(DorzakBusinessClient::class));

        $this->assertSame('cancelled', $order->fresh()->delivery_external_status);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), "/delivery-requests/{$order->id}/cancel"));
    }

    public function test_the_webhook_walks_the_order_to_out_for_delivery_and_complete(): void
    {
        $order = $this->acceptedOrder(['status' => OrderStatus::PREPARING, 'delivery_external_reference' => 'TND-9001']);

        $this->postWebhook('delivery.status_changed', ['source_order_id' => (string) $order->id, 'to' => 'en_route_customer'])
            ->assertOk();
        $this->assertSame(OrderStatus::OUT_FOR_DELIVERY, $order->fresh()->status);

        $this->postWebhook('delivery.status_changed', ['source_order_id' => (string) $order->id, 'to' => 'delivered'])
            ->assertOk();
        $order->refresh();
        $this->assertSame(OrderStatus::COMPLETE, $order->status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_the_webhook_rejects_a_forged_signature(): void
    {
        $order = $this->acceptedOrder();

        $this->withHeaders([
            'X-Dorzak-Event-Type' => 'delivery.status_changed',
            'X-Dorzak-Timestamp' => (string) now()->timestamp,
            'X-Dorzak-Signature' => str_repeat('0', 64),
        ])->postJson('/api/webhooks/dorzak-business', ['source_order_id' => (string) $order->id, 'to' => 'delivered'])
            ->assertUnauthorized();

        $this->assertSame(OrderStatus::ACCEPTED, $order->fresh()->status);
    }

    private function postWebhook(string $eventType, array $payload): TestResponse
    {
        $body = json_encode($payload);
        $timestamp = now()->timestamp;
        $signature = DorzakBusinessClient::sign('hook', $eventType, (string) $payload['source_order_id'], $body, $timestamp);

        return $this->call(
            'POST',
            '/api/webhooks/dorzak-business',
            server: [
                'HTTP_X_DORZAK_EVENT_TYPE' => $eventType,
                'HTTP_X_DORZAK_TIMESTAMP' => (string) $timestamp,
                'HTTP_X_DORZAK_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: $body,
        );
    }
}
