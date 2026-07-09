<?php

namespace Tests\Feature\Reports;

use App\Enums\StaffRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner([
            'charge_sales_tax' => true, 'tax_rate' => 10, 'timezone' => 'UTC',
        ]);
        $this->bindContext();
    }

    private function bindContext(): void
    {
        app(StoreContext::class)->setMembership(
            $this->owner->memberships()->where('store_id', $this->store->id)->with('store')->first()
        );
    }

    private function order(array $data)
    {
        $this->bindContext();

        return app(OrderService::class)->create($this->store, $data, $this->owner);
    }

    public function test_finance_aggregates_by_method_and_excludes_cancelled(): void
    {
        $p = Product::factory()->for($this->store)->create(['price' => 100, 'cost' => 40, 'stock' => 100, 'taxable' => true]);
        $this->order(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CARD', 'status' => 'COMPLETE']);
        $this->order(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CASH', 'status' => 'COMPLETE']);
        $pending = $this->order(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'TRANSFER', 'status' => 'CONFIRMING']);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/reports/finance?period=all')
            ->assertOk()
            ->assertJsonPath('data.gross_revenue', '220.00')  // 2 completed x 110
            ->assertJsonPath('data.tax_collected', '20.00')
            ->assertJsonPath('data.net_revenue', '200.00')
            ->assertJsonPath('data.pending_revenue', '110.00')
            ->assertJsonPath('data.by_method.CARD', '110.00')
            ->assertJsonPath('data.by_method.CASH', '110.00')
            ->assertJsonPath('data.completed_orders', 2);
    }

    public function test_analytics_kpis_and_gross_profit_from_cost_snapshot(): void
    {
        $p = Product::factory()->for($this->store)->create(['price' => 100, 'cost' => 40, 'stock' => 100, 'taxable' => true]);
        $this->order(['items' => [['product_id' => $p->id, 'quantity' => 2]], 'payment_method' => 'CARD', 'status' => 'COMPLETE']);

        // Changing the product cost afterwards must NOT change historical profit.
        $p->update(['cost' => 90]);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/reports/analytics?period=all')
            ->assertOk()
            ->assertJsonPath('data.kpis.orders', 1)
            ->assertJsonPath('data.kpis.revenue', '220.00')
            ->assertJsonPath('data.kpis.avg_order_value', '220.00')
            ->assertJsonPath('data.kpis.gross_profit', '120.00'); // (100-40)*2 from snapshot
    }

    public function test_top_products_ranked_by_revenue(): void
    {
        $a = Product::factory()->for($this->store)->create(['name' => 'Alpha', 'price' => 100, 'stock' => 100]);
        $b = Product::factory()->for($this->store)->create(['name' => 'Beta', 'price' => 10, 'stock' => 100]);
        $this->order(['items' => [['product_id' => $a->id, 'quantity' => 1]], 'payment_method' => 'CASH']);
        $this->order(['items' => [['product_id' => $b->id, 'quantity' => 1]], 'payment_method' => 'CASH']);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/reports/analytics?period=all')
            ->assertOk()
            ->assertJsonPath('data.top_products.0.name', 'Alpha');
    }

    public function test_inventory_health_counts(): void
    {
        Product::factory()->for($this->store)->create(['track_stock' => true, 'stock' => 100, 'min_stock' => 5]);
        Product::factory()->for($this->store)->lowStock()->create();
        Product::factory()->for($this->store)->outOfStock()->create();

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/reports/analytics?period=all')
            ->assertOk()
            ->assertJsonPath('data.inventory.total_products', 3)
            ->assertJsonPath('data.inventory.low_stock_count', 1)
            ->assertJsonPath('data.inventory.out_of_stock_count', 1);
    }

    public function test_by_category_groups_products(): void
    {
        $cat = Category::factory()->for($this->store)->create(['name' => 'Apparel']);
        Product::factory()->count(2)->for($this->store)->for($cat)->create(['stock' => 10]);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/reports/analytics?period=all')
            ->assertOk()
            ->assertJsonPath('data.by_category.0.name', 'Apparel')
            ->assertJsonPath('data.by_category.0.products_count', 2);
    }

    public function test_today_period_includes_todays_orders(): void
    {
        $p = Product::factory()->for($this->store)->create(['price' => 50, 'stock' => 100, 'taxable' => false]);
        $this->order(['items' => [['product_id' => $p->id, 'quantity' => 1]], 'payment_method' => 'CASH']);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/reports/finance?period=today')
            ->assertOk()
            ->assertJsonPath('data.gross_revenue', '50.00');
    }

    public function test_viewer_allowed_cashier_forbidden(): void
    {
        $viewer = $this->createMember(StaffRole::VIEWER, $this->store);
        $cashier = $this->createMember(StaffRole::CASHIER, $this->store);

        $this->actingAsMember($viewer)->getJson('/api/v1/reports/finance')->assertOk();
        $this->actingAsMember($viewer)->getJson('/api/v1/reports/analytics')->assertOk();
        $this->actingAsMember($cashier)->getJson('/api/v1/reports/finance')->assertForbidden();
    }

    public function test_export_is_owner_manager_only(): void
    {
        $viewer = $this->createMember(StaffRole::VIEWER, $this->store);

        $this->actingAsMember($viewer)->get('/api/v1/reports/finance/export')->assertForbidden();
        $this->actingAsMember($this->owner)->get('/api/v1/reports/finance/export')->assertOk();
    }
}
