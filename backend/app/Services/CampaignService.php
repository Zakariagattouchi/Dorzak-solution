<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Exceptions\DomainConflictException;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Scheduled marketing campaigns (premium — PlanFeature::CAMPAIGNS). A due
 * campaign is sent once to its audience (all customers or a saved segment)
 * through a REAL channel: email from the store's configured sender identity,
 * or WhatsApp through the store's own verified Business Cloud API number.
 * An unconfigured channel refuses to send, and counts are honest — sent_count
 * is actual successes, failed_count the rest.
 */
class CampaignService
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly SegmentService $segments,
        private readonly MessagingService $messaging,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(Store $store, array $data): Campaign
    {
        $this->plans->ensure($store, PlanFeature::CAMPAIGNS);

        return Campaign::create([
            'store_id' => $store->id,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'channel' => ($data['channel'] ?? 'email') === 'whatsapp' ? 'whatsapp' : 'email',
            'wa_template_name' => $data['wa_template_name'] ?? null,
            'wa_template_language' => $data['wa_template_language'] ?? null,
            'audience' => $this->normalizeAudience($data['audience'] ?? ['type' => 'all']),
            'status' => ! empty($data['scheduled_at']) ? 'scheduled' : 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);
    }

    /**
     * Send every scheduled campaign whose time has arrived. A campaign whose
     * channel became unconfigured is skipped (stays scheduled) rather than
     * being falsely marked sent.
     */
    public function dispatchDue(): int
    {
        // Runs from the scheduler with no store context, so the global store
        // scope no-ops; each campaign still sends only to its own store's
        // customers via the relation below.
        $due = Campaign::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        $dispatched = 0;
        foreach ($due as $campaign) {
            try {
                $this->send($campaign);
                $dispatched++;
            } catch (DomainConflictException $e) {
                Log::warning("Campaign {$campaign->id} skipped: {$e->getMessage()}");
            }
        }

        return $dispatched;
    }

    /** @throws DomainConflictException when the campaign's channel is not configured. */
    public function send(Campaign $campaign): void
    {
        $store = $campaign->store;

        if ($campaign->channel === 'whatsapp') {
            $this->ensureWhatsappReady($store);
            [$sent, $failed] = $this->sendWhatsapp($campaign, $store);
        } else {
            $channel = $this->messaging->emailChannel($store);

            if ($channel === null) {
                throw new DomainConflictException('CHANNEL_NOT_CONFIGURED', 'Set your email sender in Marketing → Channels before sending email campaigns.');
            }

            [$sent, $failed] = $this->sendEmail($campaign, $channel);
        }

        $campaign->update(['status' => 'sent', 'sent_at' => now(), 'sent_count' => $sent, 'failed_count' => $failed]);
    }

    /** @return array{0:int, 1:int} sent / failed */
    private function sendWhatsapp(Campaign $campaign, Store $store): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($this->audienceFor($campaign) as $customer) {
            if (empty($customer->phone)) {
                continue;
            }

            $this->messaging->sendWhatsapp($store, $customer->phone, $campaign) ? $sent++ : $failed++;
        }

        return [$sent, $failed];
    }

    /**
     * @param  array{mailer: string, from_address: string, from_name: ?string}  $channel
     * @return array{0:int, 1:int} sent / failed
     */
    private function sendEmail(Campaign $campaign, array $channel): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($this->audienceFor($campaign) as $customer) {
            if (empty($customer->email)) {
                continue;
            }

            try {
                Mail::mailer($channel['mailer'])
                    ->to($customer->email)
                    ->send(new CampaignMail($campaign, $channel['from_address'], $channel['from_name'], $customer->id));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning("Campaign {$campaign->id} email to {$customer->email} failed: {$e->getMessage()}");
            }
        }

        return [$sent, $failed];
    }

    private function ensureWhatsappReady(Store $store): void
    {
        if (! $this->messaging->settings($store)->whatsappReady()) {
            throw new DomainConflictException('CHANNEL_NOT_CONFIGURED', 'Connect your WhatsApp Business number in Marketing → Channels before sending WhatsApp campaigns.');
        }
    }

    /** @return Collection<int, Customer> */
    public function audienceFor(Campaign $campaign): Collection
    {
        $audience = $campaign->audience;

        if (($audience['type'] ?? 'all') === 'segment') {
            $segment = $campaign->store->segments()->whereKey($audience['segment_id'] ?? null)->first();

            return $segment === null ? collect() : $this->segments->members($segment)->filter(fn ($c) => $c->marketing_consent)->values();
        }

        return $campaign->store->customers()->where('marketing_consent', true)->get();
    }

    /** @param array<string, mixed> $audience */
    private function normalizeAudience(array $audience): array
    {
        if (($audience['type'] ?? 'all') === 'segment' && ! empty($audience['segment_id'])) {
            return ['type' => 'segment', 'segment_id' => (int) $audience['segment_id']];
        }

        return ['type' => 'all'];
    }
}
