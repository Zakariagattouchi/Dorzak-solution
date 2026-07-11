<?php

namespace Tests\Feature\Recurring;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSubscription;
use App\Models\Product;
use App\Models\Store;
use App\Services\RecurringOrderService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Premium feature: recurring customer orders generated on a cadence. */
class RecurringOrderTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    private Product $product;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->store = Store::factory()->create(['charge_sales_tax' => false]);
        app(StoreContext::class)->setStore($this->store);
        $this->product = Product::factory()->for($this->store)->create(['price' => 30, 'track_stock' => false]);
        $this->customer = Customer::factory()->create(['store_id' => $this->store->id]);
    }

    private function subscription(array $attrs = []): OrderSubscription
    {
        return OrderSubscription::create(array_merge([
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'cadence' => 'WEEKLY',
            'status' => 'active',
            'next_run_at' => now()->subMinute(),
        ], $attrs));
    }

    public function test_a_due_subscription_generates_an_order_and_advances(): void
    {
        $sub = $this->subscription();

        app(RecurringOrderService::class)->generateDue();

        $this->assertSame(1, Order::where('customer_id', $this->customer->id)->count());
        $sub->refresh();
        // Next run advanced one week into the future.
        $this->assertTrue($sub->next_run_at->isFuture());
        $this->assertSame('active', $sub->status);
    }

    public function test_a_paused_subscription_is_skipped(): void
    {
        $this->subscription(['status' => 'paused']);

        app(RecurringOrderService::class)->generateDue();

        $this->assertSame(0, Order::count());
    }

    public function test_a_future_subscription_is_not_due(): void
    {
        $this->subscription(['next_run_at' => now()->addWeek()]);

        app(RecurringOrderService::class)->generateDue();

        $this->assertSame(0, Order::count());
    }
}
