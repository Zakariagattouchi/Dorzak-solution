<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralProgram;
use App\Models\Store;
use Illuminate\Support\Str;

/**
 * Referral program (premium — PlanFeature::REFERRALS). Each customer has a
 * share code; a new customer's first order placed with it creates a pending
 * referral, and completing that order rewards both sides in store credit.
 */
class ReferralService
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly WalletService $wallet,
    ) {}

    /** @param array<string, mixed> $data */
    public function configure(Store $store, array $data): ReferralProgram
    {
        $this->plans->ensure($store, PlanFeature::REFERRALS);

        return ReferralProgram::updateOrCreate(
            ['store_id' => $store->id],
            [
                'enabled' => $data['enabled'] ?? true,
                'referrer_reward' => (float) ($data['referrer_reward'] ?? 15),
                'referee_reward' => (float) ($data['referee_reward'] ?? 5),
            ],
        );
    }

    public function program(Store $store): ?ReferralProgram
    {
        return ReferralProgram::firstWhere('store_id', $store->id);
    }

    /** The customer's share code, generated on first request. */
    public function codeFor(Customer $customer): string
    {
        if ($customer->referral_code === null) {
            $customer->forceFill(['referral_code' => strtoupper(Str::random(8))])->save();
        }

        return $customer->referral_code;
    }

    /**
     * Attribute a referral when a new customer uses a code on their first order.
     * Silently ignores self-referral, unknown codes, and already-referred or
     * returning customers — attribution must never block checkout.
     */
    public function attribute(Store $store, Customer $referred, string $code, int $priorOrderCount): void
    {
        $program = $this->program($store);

        if ($program === null || ! $program->enabled || $code === '') {
            return;
        }

        if ($priorOrderCount > 0 || Referral::where('referred_customer_id', $referred->id)->exists()) {
            return;
        }

        $referrer = Customer::where('store_id', $store->id)
            ->whereRaw('UPPER(referral_code) = ?', [strtoupper($code)])
            ->first();

        if ($referrer === null || $referrer->id === $referred->id) {
            return;
        }

        Referral::create([
            'store_id' => $store->id,
            'referrer_customer_id' => $referrer->id,
            'referred_customer_id' => $referred->id,
            'code' => strtoupper($code),
            'status' => 'pending',
        ]);
    }

    /** Reward both sides when a referred customer's order is placed. */
    public function rewardOnOrder(Order $order): void
    {
        if ($order->customer_id === null) {
            return;
        }

        $referral = Referral::where('store_id', $order->store_id)
            ->where('referred_customer_id', $order->customer_id)
            ->where('status', 'pending')
            ->first();

        if ($referral === null) {
            return;
        }

        $program = $this->program($order->store);

        if ($program === null || ! $program->enabled) {
            return;
        }

        $this->wallet->credit($referral->referrer, (float) $program->referrer_reward, 'Referral reward');
        $this->wallet->credit($referral->referred, (float) $program->referee_reward, 'Referral welcome');

        $referral->update(['status' => 'rewarded', 'reward_order_id' => $order->id]);
    }
}
