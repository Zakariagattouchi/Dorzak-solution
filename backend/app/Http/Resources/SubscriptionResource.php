<?php

namespace App\Http\Resources;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subscription
 */
class SubscriptionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $plan = $this->plan;

        return [
            'plan' => $plan?->code,
            'plan_name' => $plan?->name_en,
            'status' => $this->status->value,
            'price' => $this->price,
            'billing_cycle' => $this->billing_cycle,
            'renews_at' => $this->renews_at?->toIso8601String(),
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'trial_used' => $this->trial_used_at !== null,
            'plan_is_default' => (bool) ($plan?->is_default),
            'features' => $plan
                ? $plan->featureLimits->map(fn ($row) => [
                    'feature' => $row->feature->value,
                    'limit' => $row->limit_value,
                ])->values()
                : [],
            'currency' => $this->store->currency ?? 'QAR',
        ];
    }
}
