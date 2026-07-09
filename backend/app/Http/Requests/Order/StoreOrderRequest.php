<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatus;
use App\Support\StoreContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $store = app(StoreContext::class)->store();
        $storeId = $store->id;
        $methods = $store->accepted_payment_methods ?? [];

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($q) => $q
                    ->where('store_id', $storeId)->whereNull('deleted_at')->where('is_active', true)),
            ],
            'items.*.variant_id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('store_id', $storeId)->whereNull('deleted_at')],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in($methods)],
            'status' => ['sometimes', Rule::in([OrderStatus::CONFIRMING->value, OrderStatus::COMPLETE->value])],
            'notes' => ['nullable', 'string', 'max:500'],
            'source' => ['sometimes', Rule::in(['pos', 'POS'])],
        ];
    }
}
