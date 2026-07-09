<?php

namespace App\Http\Requests\Public;

use App\Http\Concerns\ResolvesPublicStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePublicOrderRequest extends FormRequest
{
    use ResolvesPublicStore;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $store = $this->resolvePublicStore($this->route('slug'));

        return [
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:120'],
            'customer.phone' => ['required', 'string', 'max:32'],
            'customer.address' => ['nullable', 'string', 'max:190'],
            'customer.address_details' => ['nullable', 'string', 'max:300'],
            'customer.city' => ['nullable', 'string', 'max:80'],
            'customer.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'customer.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'fulfillment' => ['required', Rule::in(['delivery', 'pickup', 'dine_in'])],
            'table_number' => ['nullable', 'integer', 'min:1'],
            'payment_method' => ['sometimes', Rule::in(['WHATSAPP', 'FAWRAN', 'CASH'])],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'payment_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($q) => $q
                    ->where('store_id', $store->id)
                    ->where('is_active', true)
                    ->where('show_in_online_store', true)
                    ->whereNull('deleted_at')),
            ],
            'items.*.variant_id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $store = $this->resolvePublicStore($this->route('slug'));
            $sf = $store->storefrontSetting;
            $fulfillment = (string) $this->input('fulfillment');
            $paymentMethod = (string) $this->input('payment_method', 'WHATSAPP');
            $tableNumber = $this->integer('table_number');
            $tableCount = (int) ($sf?->dine_in_table_count ?? 0);

            if ($fulfillment === 'dine_in') {
                if (! $sf?->allow_dine_in) {
                    $validator->errors()->add('fulfillment', 'Dine-in ordering is not available for this store.');
                }

                if ($tableNumber < 1) {
                    $validator->errors()->add('table_number', 'Select a table for dine-in orders.');
                }

                if ($tableCount < 1) {
                    $validator->errors()->add('table_number', 'Dine-in tables are not configured for this store.');
                }

                if ($tableCount > 0 && $tableNumber > $tableCount) {
                    $validator->errors()->add('table_number', "Table {$tableNumber} is not configured for this store.");
                }
            } elseif ($paymentMethod === 'CASH') {
                $validator->errors()->add('payment_method', 'Cash collection is only available for dine-in orders.');
            }
        });
    }
}
