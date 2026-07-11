<?php

namespace Tests\Feature\GiftCard;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\WalletService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: store credit spends at checkout. */
class StoreCreditCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner(['charge_sales_tax' => false]);
        app(StoreContext::class)->setStore($this->store);
    }

    public function test_store_credit_is_applied_and_debited_at_checkout(): void
    {
        $product = Product::factory()->for($this->store)->create(['price' => 100, 'track_stock' => false]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);
        app(WalletService::class)->credit($customer, 30, 'seed');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'payment_method' => 'CARD',
                'customer_id' => $customer->id,
                'wallet_redeem' => 30,
            ])
            ->assertCreated()
            ->assertJsonPath('data.discount', '30.00')
            ->assertJsonPath('data.total', '70.00');

        $this->assertSame('0.00', number_format(app(WalletService::class)->balance($customer->fresh()), 2));
    }
}
