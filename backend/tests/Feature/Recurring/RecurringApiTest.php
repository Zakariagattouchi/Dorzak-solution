<?php

namespace Tests\Feature\Recurring;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: recurring-order API gated by PlanFeature::RECURRING_ORDERS. */
class RecurringApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    private function payload(): array
    {
        $product = Product::factory()->for($this->store)->create();
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);

        return [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'cadence' => 'WEEKLY',
        ];
    }

    public function test_free_plan_cannot_create_recurring_orders(): void
    {
        $this->assignPlan($this->store, 'FREE');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/recurring-orders', $this->payload())
            ->assertStatus(402);
    }

    public function test_enterprise_plan_creates_a_recurring_order(): void
    {
        $this->assignPlan($this->store, 'ENTERPRISE');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/recurring-orders', $this->payload())
            ->assertCreated();
    }
}
