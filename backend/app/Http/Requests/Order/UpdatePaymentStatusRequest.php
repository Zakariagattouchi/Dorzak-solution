<?php

namespace App\Http\Requests\Order;

use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.update_status') ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_status' => ['required', Rule::in(PaymentStatus::values())],
        ];
    }
}
