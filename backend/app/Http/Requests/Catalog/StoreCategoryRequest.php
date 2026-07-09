<?php

namespace App\Http\Requests\Catalog;

use App\Support\StoreContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $storeId = app(StoreContext::class)->storeId();

        return [
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique('categories', 'name')->where('store_id', $storeId)->ignore($this->route('category')),
            ],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
