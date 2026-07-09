<?php

namespace App\Http\Requests\Catalog;

use App\Enums\Unit;
use App\Support\StoreContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $storeId = app(StoreContext::class)->storeId();
        $productId = $this->route('product'); // raw id (models are resolved in the controller)

        return [
            'name' => ['required', 'string', 'max:160'],
            'name_ar' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'reduced_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', $storeId)],
            'sku' => [
                'nullable', 'string', 'max:64',
                Rule::unique('products', 'sku')
                    ->where(fn ($q) => $q->where('store_id', $storeId)->whereNull('deleted_at'))
                    ->ignore($productId),
            ],
            'unit' => ['sometimes', Rule::in(Unit::values())],
            'image_url' => ['nullable', 'string', 'max:255'],
            'additional_images' => ['nullable', 'array', 'max:3'],
            'additional_images.*' => ['string', 'max:255'],
            'image_focus' => ['nullable', 'array'],
            'label_name' => ['nullable', 'string', 'max:40'],
            'label_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'taxable' => ['sometimes', 'boolean'],
            'track_stock' => ['sometimes', 'boolean'],
            'stock' => ['nullable', 'required_if:track_stock,true', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'show_in_online_store' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'variant_groups' => ['nullable', 'array'],
            'variant_groups.*.id' => ['required_with:variant_groups', 'string', 'max:64'],
            'variant_groups.*.name' => ['required_with:variant_groups', 'string', 'max:80'],
            'variant_groups.*.name_ar' => ['nullable', 'string', 'max:80'],
            'variant_groups.*.required' => ['required_with:variant_groups', 'boolean'],
            'variant_groups.*.options' => ['required_with:variant_groups', 'array', 'min:1'],
            'variant_groups.*.options.*.id' => ['required', 'string', 'max:64'],
            'variant_groups.*.options.*.name' => ['required', 'string', 'max:80'],
            'variant_groups.*.options.*.name_ar' => ['nullable', 'string', 'max:80'],

            'variants' => ['sometimes', 'array'],
            'variants.*.id' => ['sometimes', 'integer'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:120'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:64'],
            'variants.*.option_values' => ['nullable', 'array'],
            'variants.*.option_values.*' => ['string', 'max:64'],
            'variants.*.is_active' => ['sometimes', 'boolean'],
        ];
    }
}
