<?php

namespace App\Http\Requests\Settings;

use App\Models\DeliveryProvider;
use App\Support\StoreContext;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateStorefrontRequest extends SettingsRequest
{
    /** Slugs that would collide with platform subdomains. Docs 05 / 12 §12. */
    public const RESERVED_SLUGS = [
        'www', 'api', 'app', 'admin', 'mail', 'store', 'shop', 'dorzak',
        'help', 'support', 'blog', 'status', 'assets', 'static', 'cdn',
    ];

    protected function prepareForValidation(): void
    {
        if ($this->has('store_slug')) {
            $this->merge([
                'store_slug' => strtolower(trim((string) $this->input('store_slug'))),
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $storeId = app(StoreContext::class)->storeId();

        return [
            'online_store_enabled' => ['required', 'boolean'],
            'store_slug' => [
                'nullable',
                'required_if:online_store_enabled,true',
                'regex:/^[a-z0-9-]{3,40}$/',
                Rule::notIn(self::RESERVED_SLUGS),
                Rule::unique('storefront_settings', 'slug')->ignore($storeId, 'store_id'),
            ],
            'store_bio' => ['nullable', 'string', 'max:300'],
            'banner_url' => ['nullable', 'string', 'max:255'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'allow_delivery' => ['required', 'boolean'],
            'allow_pickup' => ['required', 'boolean'],
            'allow_dine_in' => ['sometimes', 'boolean'],
            'dine_in_table_count' => ['sometimes', 'integer', 'min:0', 'max:500'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'free_delivery_threshold' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'whatsapp_ordering_enabled' => ['required', 'boolean'],
            'fawran_enabled' => ['sometimes', 'boolean'],
            'fawran_alias' => ['nullable', 'string', 'max:80'],
            'fawran_mobile' => ['nullable', 'string', 'max:32'],
            'fawran_iban' => ['nullable', 'string', 'max:34'],
            'show_out_of_stock_online' => ['required', 'boolean'],
            'product_card_layout' => ['sometimes', 'in:vertical,horizontal'],
            'show_store_header' => ['sometimes', 'boolean'],
            'show_store_gradient' => ['sometimes', 'boolean'],
            'navbar_color' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            // Courier quotes need a store origin. Only enforced once distance
            // providers exist — pure legacy flat-fee stores are unaffected.
            if ($this->boolean('allow_delivery')
                && ! app(StoreContext::class)->store()?->hasLocation()
                && DeliveryProvider::query()->where('is_active', true)->exists()) {
                $validator->errors()->add('allow_delivery', 'Set your store location in Business settings before enabling delivery.');
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'store_slug.regex' => 'The store link may only contain lowercase letters, numbers, and hyphens (3–40 characters).',
            'store_slug.not_in' => 'That store link is reserved. Please choose another.',
            'store_slug.unique' => 'That store link is already taken.',
        ];
    }
}
