<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class UploadCategoryImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.manage') ?? false;
    }

    public function rules(): array
    {
        return ['file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']];
    }
}
