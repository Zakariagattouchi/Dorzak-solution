<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Support\StoreContext;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pins the DemoSeeder to the recovered mockData shape so the reconstructed React app
 * renders identically against the API. Docs 10.
 */
class DemoSeederParityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        // Read back within the demo store's scope.
        $store = Store::firstWhere('name', 'Dorzak Merchant');
        app(StoreContext::class)->setStore($store);
    }

    public function test_store_and_subscription(): void
    {
        $store = Store::firstWhere('name', 'Dorzak Merchant');
        $this->assertSame('USD', $store->currency);
        $this->assertSame('8.50', (string) $store->tax_rate);
        $this->assertSame('PRO', $store->subscription->plan->value);
        $this->assertSame('dorzak-merchant', $store->storefrontSetting->slug);
    }

    public function test_six_products_with_hoodie_variants(): void
    {
        $this->assertSame(6, Product::count());

        $hoodie = Product::where('name', 'Dorzak Signature Cotton Hoodie')->firstOrFail();
        $this->assertCount(3, $hoodie->variants);
        // Opening stock was 45 (15+20+10); the seeded sale of 2 leaves 43. The
        // invariant that matters: parent stock always equals the variant sum.
        $this->assertSame($hoodie->variants->sum('stock'), $hoodie->stock);
        $this->assertSame(43, $hoodie->stock);
    }

    public function test_three_orders_with_consistent_money(): void
    {
        $this->assertSame(3, Order::count());

        foreach (Order::with('items')->get() as $order) {
            $subtotal = (float) $order->items->sum('line_total');
            $expectedTotal = round($subtotal - (float) $order->discount + (float) $order->tax_amount + (float) $order->delivery_fee, 2);
            $this->assertSame(number_format($expectedTotal, 2, '.', ''), (string) $order->total, "Order {$order->order_number} total mismatch");
        }
    }

    public function test_completed_orders_updated_customer_counters(): void
    {
        $sarah = Customer::where('name', 'Sarah Jenkins')->firstOrFail();
        $this->assertSame(1, $sarah->total_orders);
        $this->assertGreaterThan(0, (float) $sarah->total_spent);
    }
}
