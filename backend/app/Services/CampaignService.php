<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * Scheduled marketing campaigns (premium — PlanFeature::CAMPAIGNS). A due
 * campaign is sent once to its audience (all customers or a saved segment),
 * to those with an email on file.
 */
class CampaignService
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly SegmentService $segments,
        private readonly WhatsAppSender $whatsapp,
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
            'audience' => $this->normalizeAudience($data['audience'] ?? ['type' => 'all']),
            'status' => ! empty($data['scheduled_at']) ? 'scheduled' : 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);
    }

    /** Send every scheduled campaign whose time has arrived. */
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

        foreach ($due as $campaign) {
            $this->send($campaign);
        }

        return $due->count();
    }

    public function send(Campaign $campaign): void
    {
        $sent = 0;
        $viaWhatsapp = $campaign->channel === 'whatsapp';

        foreach ($this->audienceFor($campaign) as $customer) {
            if ($viaWhatsapp) {
                if (! empty($customer->phone)) {
                    $this->whatsapp->send($customer->phone, $campaign->subject.' — '.$campaign->body);
                    $sent++;
                }
            } elseif (! empty($customer->email)) {
                Mail::to($customer->email)->send(new CampaignMail($campaign));
                $sent++;
            }
        }

        $campaign->update(['status' => 'sent', 'sent_at' => now(), 'sent_count' => $sent]);
    }

    /** @return Collection<int, \App\Models\Customer> */
    public function audienceFor(Campaign $campaign): Collection
    {
        $audience = $campaign->audience;

        if (($audience['type'] ?? 'all') === 'segment') {
            $segment = $campaign->store->segments()->whereKey($audience['segment_id'] ?? null)->first();

            return $segment === null ? collect() : $this->segments->members($segment);
        }

        return $campaign->store->customers()->get();
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
