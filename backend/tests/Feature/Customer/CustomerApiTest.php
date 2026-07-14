<?php

namespace Tests\Feature\Customer;

use App\Enums\StaffRole;
use App\Jobs\ImportCustomersJob;
use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_search_matches_name_email_phone(): void
    {
        Customer::factory()->for($this->store)->create(['name' => 'Sarah Jenkins', 'email' => 'sarah@x.com', 'phone' => '+1 555-0144']);
        Customer::factory()->for($this->store)->create(['name' => 'David Miller', 'email' => 'd@y.com', 'phone' => '+1 555-0812']);

        $this->actingAsMember($this->owner)->getJson('/api/v1/customers?search=jenkins')->assertJsonCount(1, 'data');
        $this->actingAsMember($this->owner)->getJson('/api/v1/customers?search=0812')->assertJsonCount(1, 'data');
    }

    public function test_sort_by_balance(): void
    {
        Customer::factory()->for($this->store)->create(['name' => 'Low', 'total_spent' => 10]);
        Customer::factory()->for($this->store)->create(['name' => 'High', 'total_spent' => 500]);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/customers?sort=-total_spent')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'High');
    }

    public function test_meta_summary_covers_whole_store(): void
    {
        Customer::factory()->for($this->store)->create(['total_spent' => 100]);
        Customer::factory()->for($this->store)->create(['total_spent' => 300]);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/customers?per_page=1')
            ->assertOk()
            ->assertJsonPath('meta.summary.count', 2)
            ->assertJsonPath('meta.summary.total_spent', '400.00')
            ->assertJsonPath('meta.summary.avg_ltv', '200.00');
    }

    public function test_create_requires_name_and_phone(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/customers', ['email' => 'x@y.com'])
            ->assertStatus(422)->assertJsonValidationErrors(['name', 'phone']);
    }

    public function test_create_customer_normalizes_phone(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/customers', ['name' => 'Jane', 'phone' => '+1 (555) 010-2345'])
            ->assertCreated();

        $this->assertDatabaseHas('customers', ['name' => 'Jane', 'phone_normalized' => '15550102345']);
    }

    public function test_duplicate_phone_returns_422_with_duplicate_id(): void
    {
        $existing = Customer::factory()->for($this->store)->create(['phone' => '+1 555-0144']);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/customers', ['name' => 'Dup', 'phone' => '1-555-0144'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone')
            ->assertJsonPath('duplicate_customer_id', $existing->id);
    }

    public function test_update_customer(): void
    {
        $customer = Customer::factory()->for($this->store)->create();

        $this->actingAsMember($this->owner)
            ->putJson("/api/v1/customers/{$customer->id}", ['name' => 'Renamed', 'phone' => $customer->phone])
            ->assertOk()->assertJsonPath('data.name', 'Renamed');
    }

    public function test_show_includes_recent_orders_key(): void
    {
        $customer = Customer::factory()->for($this->store)->create();

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.recent_orders', []);
    }

    public function test_delete_is_soft_and_owner_only(): void
    {
        $customer = Customer::factory()->for($this->store)->create();
        $cashier = $this->createMember(StaffRole::CASHIER, $this->store);

        $this->actingAsMember($cashier)->deleteJson("/api/v1/customers/{$customer->id}")->assertForbidden();
        $this->actingAsMember($this->owner)->deleteJson("/api/v1/customers/{$customer->id}")->assertNoContent();
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_deleted_phone_can_be_reused(): void
    {
        $customer = Customer::factory()->for($this->store)->create(['phone' => '+1 555-9999']);
        $customer->delete();

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/customers', ['name' => 'New', 'phone' => '+1 555-9999'])
            ->assertCreated();
    }

    public function test_viewer_can_list_but_not_create(): void
    {
        $viewer = $this->createMember(StaffRole::VIEWER, $this->store);

        $this->actingAsMember($viewer)->getJson('/api/v1/customers')->assertOk();
        $this->actingAsMember($viewer)->postJson('/api/v1/customers', ['name' => 'X', 'phone' => '1'])->assertForbidden();
    }

    public function test_export_csv_has_header_and_rows(): void
    {
        Customer::factory()->for($this->store)->create(['name' => 'Sarah']);

        $response = $this->actingAsMember($this->owner)->get('/api/v1/customers/export');
        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('name,phone,email', $csv);
        $this->assertStringContainsString('Sarah', $csv);
    }

    public function test_import_creates_rows_and_reports_errors(): void
    {
        $csv = "name,phone,email\nAlice,+1 555-1000,alice@x.com\n,+1 555-2000,bad@x.com\nBob,+1 555-3000,bob@x.com\n";
        $file = UploadedFile::fake()->createWithContent('customers.csv', $csv);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/customers/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('data.imported', 2)
            ->assertJsonPath('data.errors.0.row', 3);

        $this->assertDatabaseHas('customers', ['name' => 'Alice']);
        $this->assertDatabaseHas('customers', ['name' => 'Bob']);
    }

    public function test_large_import_is_queued(): void
    {
        Queue::fake();
        $rows = "name,phone\n";
        for ($i = 0; $i < 600; $i++) {
            $rows .= "Cust{$i},+1 555-".str_pad((string) $i, 4, '0', STR_PAD_LEFT)."\n";
        }
        $file = UploadedFile::fake()->createWithContent('big.csv', $rows);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/customers/import', ['file' => $file])
            ->assertStatus(202)
            ->assertJsonPath('data.queued', true);

        Queue::assertPushed(ImportCustomersJob::class);
    }

    public function test_cross_tenant_customer_is_not_found(): void
    {
        $foreign = Customer::factory()->create();

        $this->actingAsMember($this->owner)
            ->getJson("/api/v1/customers/{$foreign->id}")
            ->assertNotFound();
    }
}
