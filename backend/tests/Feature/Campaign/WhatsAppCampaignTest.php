<?php

namespace Tests\Feature\Campaign;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Store;
use App\Services\CampaignService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Premium feature: campaigns can be sent over WhatsApp (to customers with a phone). */
class WhatsAppCampaignTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->store = Store::factory()->create();
        app(StoreContext::class)->setStore($this->store);
    }

    public function test_a_whatsapp_campaign_targets_customers_with_a_phone(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500001']);
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500002']);
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '']); // unreachable

        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'Sale', 'body' => '20% off',
            'channel' => 'whatsapp', 'audience' => ['type' => 'all'],
            'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
        ]);

        app(CampaignService::class)->dispatchDue();

        $campaign->refresh();
        $this->assertSame('sent', $campaign->status);
        $this->assertSame(2, $campaign->sent_count); // phone-reachable only
        Mail::assertNothingSent(); // WhatsApp channel, not email
    }
}
