<?php

namespace Tests\Feature\Marketing;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\MessagingSetting;
use App\Models\Store;
use App\Models\User;
use App\Services\CampaignService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Marketing channels must be REAL: WhatsApp needs per-store Business API
 * credentials verified against the Graph API; email needs a configured sender.
 * An unconfigured channel refuses to send — it never pretends to deliver.
 */
class MessagingChannelsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
        $this->assignPlan($this->store, 'ENTERPRISE');
        app(StoreContext::class)->setStore($this->store);
    }

    public function test_messaging_settings_are_saved_and_secrets_are_masked_on_read(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/messaging', [
                'email_from_name' => 'Dorzak Merchant',
                'email_from_address' => 'hello@dorzak.shop',
                'smtp_host' => 'smtp.example.com', 'smtp_port' => 587,
                'smtp_username' => 'apikey', 'smtp_password' => 'secret-123', 'smtp_encryption' => 'tls',
                'whatsapp_token' => 'wa-token-abc', 'whatsapp_phone_number_id' => '1234567890',
            ])
            ->assertOk();

        $res = $this->actingAsMember($this->owner)->getJson('/api/v1/settings/messaging')->assertOk();

        // Secrets never round-trip — only their presence does.
        $this->assertNull($res->json('messaging.smtp_password'));
        $this->assertNull($res->json('messaging.whatsapp_token'));
        $this->assertTrue($res->json('messaging.has_smtp_password'));
        $this->assertTrue($res->json('messaging.has_whatsapp_token'));
        $this->assertSame('hello@dorzak.shop', $res->json('messaging.email_from_address'));
        // Stored encrypted, decrypts transparently.
        $this->assertSame('wa-token-abc', MessagingSetting::first()->whatsapp_token);
    }

    public function test_whatsapp_verify_marks_the_channel_connected_via_graph_api(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['display_phone_number' => '+974 5550 0000', 'verified_name' => 'Dorzak'], 200)]);
        MessagingSetting::create(['store_id' => $this->store->id, 'whatsapp_token' => 't', 'whatsapp_phone_number_id' => '123']);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/settings/messaging/verify-whatsapp')
            ->assertOk()
            ->assertJsonPath('connected', true)
            ->assertJsonPath('display_phone_number', '+974 5550 0000');

        $this->assertNotNull(MessagingSetting::first()->whatsapp_connected_at);
    }

    public function test_whatsapp_verify_failure_records_the_error(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid OAuth access token.']], 401)]);
        MessagingSetting::create(['store_id' => $this->store->id, 'whatsapp_token' => 'bad', 'whatsapp_phone_number_id' => '123']);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/settings/messaging/verify-whatsapp')
            ->assertStatus(422)
            ->assertJsonPath('connected', false);

        $this->assertNull(MessagingSetting::first()->whatsapp_connected_at);
        $this->assertNotNull(MessagingSetting::first()->whatsapp_error);
    }

    public function test_a_whatsapp_campaign_refuses_to_send_when_not_connected(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500001']);
        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'Sale', 'body' => 'Now',
            'channel' => 'whatsapp', 'audience' => ['type' => 'all'], 'status' => 'draft',
        ]);

        // DomainConflictException renders as 409 with a stable machine code.
        $this->actingAsMember($this->owner)
            ->postJson("/api/v1/campaigns/{$campaign->id}/send")
            ->assertStatus(409)
            ->assertJsonPath('code', 'CHANNEL_NOT_CONFIGURED');

        $this->assertSame('draft', $campaign->refresh()->status); // untouched, not "sent"
    }

    public function test_a_connected_whatsapp_campaign_sends_real_graph_api_messages(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]], 200)]);
        MessagingSetting::create([
            'store_id' => $this->store->id, 'whatsapp_token' => 't',
            'whatsapp_phone_number_id' => '123', 'whatsapp_connected_at' => now(),
        ]);
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500001']);
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500002']);

        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'Sale', 'body' => '20% off',
            'channel' => 'whatsapp', 'audience' => ['type' => 'all'],
            'wa_template_name' => 'weekend_sale', 'wa_template_language' => 'en',
            'status' => 'draft',
        ]);

        app(CampaignService::class)->send($campaign);

        $campaign->refresh();
        $this->assertSame(2, $campaign->sent_count);
        $this->assertSame(0, $campaign->failed_count);
        // Template payload went to the store's own phone-number endpoint.
        Http::assertSent(fn ($req) => str_contains($req->url(), '/123/messages')
            && $req['type'] === 'template'
            && $req['template']['name'] === 'weekend_sale');
    }

    public function test_failed_whatsapp_sends_are_counted_not_hidden(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::sequence()
            ->push(['messages' => [['id' => 'wamid.1']]], 200)
            ->push(['error' => ['message' => 'unreachable']], 400)]);
        MessagingSetting::create([
            'store_id' => $this->store->id, 'whatsapp_token' => 't',
            'whatsapp_phone_number_id' => '123', 'whatsapp_connected_at' => now(),
        ]);
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500001']);
        Customer::factory()->create(['store_id' => $this->store->id, 'phone' => '+97455500002']);

        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'S', 'body' => 'B',
            'channel' => 'whatsapp', 'audience' => ['type' => 'all'], 'status' => 'draft',
        ]);

        app(CampaignService::class)->send($campaign);

        $this->assertSame(1, $campaign->refresh()->sent_count);
        $this->assertSame(1, $campaign->failed_count);
    }

    public function test_an_email_campaign_refuses_to_send_without_a_sender_address(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'a@example.com']);
        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'S', 'body' => 'B',
            'channel' => 'email', 'audience' => ['type' => 'all'], 'status' => 'draft',
        ]);

        $this->actingAsMember($this->owner)
            ->postJson("/api/v1/campaigns/{$campaign->id}/send")
            ->assertStatus(409)
            ->assertJsonPath('code', 'CHANNEL_NOT_CONFIGURED');
    }

    public function test_a_configured_email_campaign_sends_from_the_store_identity(): void
    {
        MessagingSetting::create([
            'store_id' => $this->store->id,
            'email_from_name' => 'Dorzak Merchant', 'email_from_address' => 'hello@dorzak.shop',
        ]);
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'a@example.com']);

        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'S', 'body' => 'B',
            'channel' => 'email', 'audience' => ['type' => 'all'], 'status' => 'draft',
        ]);

        app(CampaignService::class)->send($campaign);

        $this->assertSame(1, $campaign->refresh()->sent_count);
        Mail::assertSent(\App\Mail\CampaignMail::class, fn ($mail) => $mail->hasTo('a@example.com') && $mail->hasFrom('hello@dorzak.shop', 'Dorzak Merchant'));
    }

    public function test_test_email_endpoint_sends_to_the_requester(): void
    {
        MessagingSetting::create([
            'store_id' => $this->store->id,
            'email_from_name' => 'Dorzak', 'email_from_address' => 'hello@dorzak.shop',
        ]);

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/settings/messaging/test-email')
            ->assertOk();

        Mail::assertSent(\App\Mail\TestChannelMail::class, fn ($mail) => $mail->hasTo($this->owner->email));
    }
}
