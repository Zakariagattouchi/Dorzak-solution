<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderDeliveryFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.update_status') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'delivery_fee' => ['required', 'numeric', 'min:0', 'max:99999'],
        ];
    }
}
