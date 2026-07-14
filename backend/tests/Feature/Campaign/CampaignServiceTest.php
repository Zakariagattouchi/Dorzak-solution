<?php

namespace Tests\Feature\Campaign;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\CustomerSegment;
use App\Models\MessagingSetting;
use App\Models\Store;
use App\Services\CampaignService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Premium feature: scheduled marketing campaigns to customers. */
class CampaignServiceTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->store = Store::factory()->create();
        app(StoreContext::class)->setStore($this->store);
        // Email campaigns require a configured sender identity.
        MessagingSetting::create([
            'store_id' => $this->store->id,
            'email_from_name' => 'Test Store', 'email_from_address' => 'hello@test.shop',
        ]);
    }

    private function campaign(array $attrs = []): Campaign
    {
        return Campaign::create(array_merge([
            'store_id' => $this->store->id,
            'subject' => 'Weekend sale',
            'body' => '20% off everything.',
            'audience' => ['type' => 'all'],
            'status' => 'scheduled',
            'scheduled_at' => now()->subMinute(),
        ], $attrs));
    }

    public function test_a_due_campaign_sends_to_customers_with_email(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'a@example.com', 'marketing_consent' => true]);
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'b@example.com', 'marketing_consent' => true]);
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => null]); // unreachable
        $campaign = $this->campaign();

        app(CampaignService::class)->dispatchDue();

        $campaign->refresh();
        $this->assertSame('sent', $campaign->status);
        $this->assertSame(2, $campaign->sent_count);
    }

    public function test_a_future_campaign_is_not_sent_yet(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'a@example.com']);
        $campaign = $this->campaign(['scheduled_at' => now()->addDay()]);

        app(CampaignService::class)->dispatchDue();

        $this->assertSame('scheduled', $campaign->refresh()->status);
    }

    public function test_a_campaign_can_target_a_saved_segment(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'vip@example.com', 'total_spent' => 500, 'marketing_consent' => true]);
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'small@example.com', 'total_spent' => 5, 'marketing_consent' => true]);
        $segment = CustomerSegment::create(['store_id' => $this->store->id, 'name' => 'VIP', 'rules' => ['min_spent' => 100]]);
        $campaign = $this->campaign(['audience' => ['type' => 'segment', 'segment_id' => $segment->id]]);

        app(CampaignService::class)->dispatchDue();

        $this->assertSame(1, $campaign->refresh()->sent_count);
    }
}
