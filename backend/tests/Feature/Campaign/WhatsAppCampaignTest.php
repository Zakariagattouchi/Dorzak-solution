<?php

namespace Tests\Feature\Campaign;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\MessagingSetting;
use App\Models\Store;
use App\Services\CampaignService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * WhatsApp campaigns go through the store's own verified Business Cloud API
 * number — a store without a connected number cannot send, and the scheduler
 * skips (never falsely completes) such campaigns.
 */
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

    private function dueWhatsappCampaign(): Campaign
    {
        return Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'Sale', 'body' => '20% off',
            'channel' => 'whatsapp', 'audience' => ['type' => 'all'],
            'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
        ]);
    }

    public function test_a_connected_store_sends_to_phone_reachable_customers_only(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]], 200)]);
        MessagingSetting::create([
            'store_id' => $this->store->id, 'whatsapp_token' => 't',
            'whatsapp_phone_number_id' => '123', 'whatsapp_connected_at' => now(),
        ]);
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500001', 'marketing_consent' => true]);
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500002', 'marketing_consent' => true]);
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '']); // unreachable

        $campaign = $this->dueWhatsappCampaign();

        app(CampaignService::class)->dispatchDue();

        $campaign->refresh();
        $this->assertSame('sent', $campaign->status);
        $this->assertSame(2, $campaign->sent_count);
        Mail::assertNothingSent(); // WhatsApp channel, not email
        Http::assertSentCount(2);
    }

    public function test_an_unconnected_store_is_skipped_by_the_scheduler_not_marked_sent(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500001']);
        $campaign = $this->dueWhatsappCampaign();

        app(CampaignService::class)->dispatchDue();

        // Still scheduled: it sends for real once the channel is connected.
        $this->assertSame('scheduled', $campaign->refresh()->status);
        $this->assertSame(0, $campaign->sent_count);
    }
}
