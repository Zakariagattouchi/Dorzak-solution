<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\WalletEntry;
use App\Services\PlanGate;
use App\Services\ReferralService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Referral program config (premium — PlanFeature::REFERRALS). */
class ReferralController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
        private readonly ReferralService $referrals,
    ) {}

    public function show(): JsonResponse
    {
        $store = $this->context->store();
        $program = $this->referrals->program($store);

        return response()->json([
            'referral' => $program ? [
                'enabled' => $program->enabled,
                'referrer_reward' => (float) $program->referrer_reward,
                'referee_reward' => (float) $program->referee_reward,
            ] : null,
            'stats' => [
                'rewarded' => Referral::where('status', 'rewarded')->count(),
                'pending' => Referral::where('status', 'pending')->count(),
                'credit_issued' => (float) WalletEntry::whereIn('reason', ['Referral reward', 'Referral welcome'])->sum('amount'),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $store = $this->context->store();
        $this->plans->ensure($store, PlanFeature::REFERRALS);
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'referrer_reward' => ['required', 'numeric', 'min:0'],
            'referee_reward' => ['required', 'numeric', 'min:0'],
        ]);

        $this->referrals->configure($store, $data);

        return $this->show();
    }
}
