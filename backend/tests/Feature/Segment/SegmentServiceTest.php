<?php

namespace Tests\Feature\Segment;

use App\Models\Customer;
use App\Models\CustomerSegment;
use App\Models\Store;
use App\Services\SegmentService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: saved customer segments defined by rules. */
class SegmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create();
        app(StoreContext::class)->setStore($this->store);
    }

    private function customer(int $orders, float $spent): Customer
    {
        return Customer::factory()->create([
            'store_id' => $this->store->id, 'total_orders' => $orders, 'total_spent' => $spent,
        ]);
    }

    public function test_rules_filter_members_by_orders_and_spend(): void
    {
        $this->customer(5, 500); // VIP
        $this->customer(1, 20);  // small
        $this->customer(0, 0);   // lead

        $segment = CustomerSegment::create([
            'store_id' => $this->store->id, 'name' => 'VIPs',
            'rules' => ['min_orders' => 2, 'min_spent' => 100],
        ]);

        $this->assertSame(1, app(SegmentService::class)->count($segment));
    }

    public function test_blank_rules_match_everyone(): void
    {
        $this->customer(1, 10);
        $this->customer(3, 300);

        $segment = CustomerSegment::create([
            'store_id' => $this->store->id, 'name' => 'All', 'rules' => [],
        ]);

        $this->assertSame(2, app(SegmentService::class)->count($segment));
    }

    public function test_max_bounds_are_respected(): void
    {
        $this->customer(1, 10);  // new/small — matches "new" segment
        $this->customer(9, 900); // whale — excluded by max

        $segment = CustomerSegment::create([
            'store_id' => $this->store->id, 'name' => 'New', 'rules' => ['max_orders' => 2],
        ]);

        $this->assertSame(1, app(SegmentService::class)->count($segment));
    }
}
