<?php

namespace Tests\Feature\Marketing;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\MessagingSetting;
use App\Models\Product;
use App\Models\Store;
use App\Services\CampaignService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/** Marketing consent: campaigns reach only opted-in customers; one-click unsubscribe. */
class ConsentTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->store = Store::factory()->create();
        app(StoreContext::class)->setStore($this->store);
        MessagingSetting::create([
            'store_id' => $this->store->id,
            'email_from_name' => 'Test', 'email_from_address' => 'hello@test.shop',
        ]);
    }

    public function test_campaign_audience_includes_only_consented_customers(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'yes@example.com', 'marketing_consent' => true]);
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'no@example.com', 'marketing_consent' => false]);

        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'S', 'body' => 'B',
            'channel' => 'email', 'audience' => ['type' => 'all'], 'status' => 'draft',
        ]);

        app(CampaignService::class)->send($campaign);

        $this->assertSame(1, $campaign->refresh()->sent_count);
    }

    public function test_signed_unsubscribe_link_revokes_consent(): void
    {
        $customer = Customer::factory()->create(['store_id' => $this->store->id, 'marketing_consent' => true]);
        $url = URL::signedRoute('public.unsubscribe', ['customer' => $customer->id]);

        $this->get($url)->assertOk()->assertSee('unsubscribed');

        $this->assertFalse($customer->fresh()->marketing_consent);
    }

    public function test_unsigned_unsubscribe_is_rejected(): void
    {
        $customer = Customer::factory()->create(['store_id' => $this->store->id, 'marketing_consent' => true]);

        $this->get("/api/public/unsubscribe/{$customer->id}")->assertForbidden();

        $this->assertTrue($customer->fresh()->marketing_consent);
    }

    public function test_public_checkout_records_consent(): void
    {
        // Happy-path setup copied from tests/Feature/PublicStorefront/PublicOrderDeliveryTest.php
        // (pickup branch — avoids delivery-quote setup entirely).
        ['store' => $store] = $this->createStoreWithOwner(['charge_sales_tax' => false]);
        $store->storefrontSetting->update([
            'online_store_enabled' => true, 'slug' => 'consent-shop',
            'allow_pickup' => true, 'min_order_amount' => 0,
        ]);
        $this->assignPlan($store, 'PRO');

        $product = Product::factory()->for($store)->create([
            'price' => 30, 'stock' => 50, 'taxable' => false, 'is_active' => true, 'show_in_online_store' => true,
        ]);

        $this->postJson('/api/public/stores/consent-shop/orders', [
            'customer' => ['name' => 'Jane', 'phone' => '+974 5555 9999'],
            'fulfillment' => 'pickup',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'marketing_consent' => true,
        ])->assertCreated();

        $customer = Customer::withoutGlobalScopes()->where('store_id', $store->id)->first();
        $this->assertTrue($customer->marketing_consent);
    }
}
