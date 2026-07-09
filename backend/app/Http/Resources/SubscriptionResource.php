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
        return [
            'plan' => $this->plan->value,
            'status' => $this->status->value,
            'price' => $this->price,
            'billing_cycle' => $this->billing_cycle,
            'renews_at' => $this->renews_at?->toIso8601String(),
            'features' => $this->plan->features(),
            'currency' => $this->store->currency ?? 'QAR',
        ];
    }
}
