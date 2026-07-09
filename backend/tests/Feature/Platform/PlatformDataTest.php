<?php

namespace Tests\Feature\Platform;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PlatformDataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_platform_admin' => true]);
    }

    public function test_customers_list_spans_all_stores(): void
    {
        ['store' => $a] = $this->createStoreWithOwner();
        ['store' => $b] = $this->createStoreWithOwner();
        Customer::factory()->create(['store_id' => $a->id, 'name' => 'Alpha Buyer']);
        Customer::factory()->create(['store_id' => $b->id, 'name' => 'Beta Buyer']);

        $names = collect(
            $this->actingAsMember($this->admin)->getJson('/api/v1/platform/customers')->assertOk()->json('data')
        )->pluck('name');

        $this->assertContains('Alpha Buyer', $names->all());
        $this->assertContains('Beta Buyer', $names->all());
    }

    public function test_products_list_spans_all_stores(): void
    {
        ['store' => $a] = $this->createStoreWithOwner();
        Product::factory()->create(['store_id' => $a->id, 'name' => 'Cross-Tenant Widget']);

        $this->actingAsMember($this->admin)
            ->getJson('/api/v1/platform/products?search=Widget')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Cross-Tenant Widget');
    }

    public function test_customers_export_streams_csv(): void
    {
        ['store' => $a] = $this->createStoreWithOwner();
        Customer::factory()->create(['store_id' => $a->id, 'name' => 'Export Me']);

        $response = $this->actingAsMember($this->admin)->get('/api/v1/platform/customers/export');
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('Export Me', $response->streamedContent());
    }

    public function test_stores_export_does_not_collide_with_store_show(): void
    {
        $this->createStoreWithOwner();

        $this->actingAsMember($this->admin)
            ->get('/api/v1/platform/stores/export')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_import_customers_into_a_store(): void
    {
        ['store' => $store] = $this->createStoreWithOwner();

        $csv = "name,phone,email\nImported One,555-1,one@x.com\nImported Two,555-2,two@x.com\n";
        $file = UploadedFile::fake()->createWithContent('customers.csv', $csv);

        $this->actingAsMember($this->admin)
            ->postJson('/api/v1/platform/customers/import', ['store_id' => $store->id, 'file' => $file])
            ->assertOk();

        $this->assertDatabaseHas('customers', ['store_id' => $store->id, 'name' => 'Imported One']);
        $this->assertDatabaseHas('platform_audit_logs', ['action' => 'customers.import']);
    }

    public function test_regular_user_cannot_reach_platform_data(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();

        $this->actingAsMember($owner)->getJson('/api/v1/platform/customers')->assertForbidden();
        $this->actingAsMember($owner)->get('/api/v1/platform/products/export')->assertForbidden();
    }
}
