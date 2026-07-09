<?php

namespace App\Http\Requests\Settings;

class UploadStorefrontMediaRequest extends SettingsRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
