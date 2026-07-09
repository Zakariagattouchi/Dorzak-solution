<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 */
class CustomerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'address_details' => $this->address_details,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'tax_id' => $this->tax_id,
            'notes' => $this->notes,
            'total_orders' => $this->total_orders,
            'total_spent' => $this->total_spent,
            'created_at' => $this->created_at?->toIso8601String(),
            // recent_orders is attached by the controller's show() from TP-06 onward.
            'recent_orders' => $this->when(isset($this->recent_orders), fn () => $this->recent_orders),
        ];
    }
}
