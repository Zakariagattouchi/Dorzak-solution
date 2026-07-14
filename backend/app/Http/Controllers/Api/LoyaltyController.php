<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\LoyaltyAccount;
use App\Services\LoyaltyService;
use App\Services\PlanGate;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Loyalty program configuration (premium — PlanFeature::LOYALTY). Reading is
 * open to any member so the UI can show current settings; writing requires the
 * capability and the settings.manage ability.
 */
class LoyaltyController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
        private readonly LoyaltyService $loyalty,
    ) {}

    /** GET /settings/loyalty */
    public function show(): JsonResponse
    {
        $program = $this->loyalty->program($this->context->store());

        return response()->json([
            'loyalty' => $program ? [
                'enabled' => $program->enabled,
                'earn_points_per_currency' => $program->earn_points_per_currency,
                'redeem_points' => $program->redeem_points,
                'redeem_value' => (float) $program->redeem_value,
            ] : null,
            'stats' => [
                'members' => LoyaltyAccount::where('points', '>', 0)->count(),
                'points_outstanding' => (int) LoyaltyAccount::sum('points'),
            ],
        ]);
    }

    /** PUT /settings/loyalty */
    public function update(Request $request): JsonResponse
    {
        $store = $this->context->store();
        $this->plans->ensure($store, PlanFeature::LOYALTY);
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'earn_points_per_currency' => ['required', 'integer', 'min:0', 'max:1000'],
            'redeem_points' => ['required', 'integer', 'min:1'],
            'redeem_value' => ['required', 'numeric', 'min:0'],
        ]);

        $this->loyalty->configure($store, $data);

        return $this->show();
    }
}
