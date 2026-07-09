<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Console\Command;

/**
 * Reverts expired trials to the default (free) plan. Scheduled daily; safe to
 * re-run. trial_used_at stays set so the store can never trial again.
 */
class ExpireTrials extends Command
{
    protected $signature = 'subscriptions:expire-trials';

    protected $description = 'Revert TRIALING subscriptions whose trial_ends_at has passed to the default plan';

    public function handle(): int
    {
        $default = Plan::default();

        if ($default === null) {
            $this->error('No default plan configured; refusing to expire trials.');

            return self::FAILURE;
        }

        $expired = Subscription::query()
            ->where('status', SubscriptionStatus::TRIALING->value)
            ->where('trial_ends_at', '<=', now())
            ->get();

        foreach ($expired as $subscription) {
            $subscription->update([
                'plan_id' => $default->id,
                'status' => SubscriptionStatus::ACTIVE,
                'trial_ends_at' => null,
            ]);
        }

        $this->info("Expired {$expired->count()} trial(s).");

        return self::SUCCESS;
    }
}
