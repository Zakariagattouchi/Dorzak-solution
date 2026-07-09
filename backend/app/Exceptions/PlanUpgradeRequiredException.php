<?php

namespace App\Exceptions;

use App\Enums\PlanFeature;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * The current plan doesn't grant (or has exhausted the limit on) a capability.
 * Maps to HTTP 402 with a stable `code` the frontend turns into an upgrade
 * prompt, plus the offending `feature`. See doc 13 §3.
 */
class PlanUpgradeRequiredException extends RuntimeException
{
    public function __construct(
        public readonly PlanFeature $feature,
        string $message = 'Your plan does not include this feature.',
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'code' => 'PLAN_UPGRADE_REQUIRED',
            'feature' => $this->feature->value,
        ], 402);
    }
}
