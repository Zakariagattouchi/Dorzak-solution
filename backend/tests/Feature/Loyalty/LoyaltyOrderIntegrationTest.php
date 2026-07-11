<?php

namespace Tests\Feature\Loyalty;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\LoyaltyService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: placing a completed order automatically accrues loyalty points. */
class LoyaltyOrderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner([
            'charge_sales_tax' => false,
        ]);
        app(StoreContext::class)->setStore($this->store);
        app(LoyaltyService::class)->configure($this->store, [
            'enabled' => true, 'earn_points_per_currency' => 1, 'redeem_points' => 100, 'redeem_value' => 5,
        ]);
    }

    public function test_a_completed_pos_order_accrues_points_to_its_customer(): void
    {
        $product = Product::factory()->for($this->store)->create(['price' => 100, 'track_stock' => false]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
                'payment_method' => 'CARD',
                'customer_id' => $customer->id,
            ])
            ->assertCreated();

        // 200 total * 1 point per unit = 200 points.
        $this->assertSame(200, app(LoyaltyService::class)->balance($customer));
    }
}
