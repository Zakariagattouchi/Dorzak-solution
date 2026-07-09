<?php

namespace App\Http\Requests\Settings;

class UpdateReceiptsRequest extends SettingsRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'header' => ['nullable', 'string', 'max:160'],
            'footer' => ['nullable', 'string', 'max:160'],
            'show_logo' => ['required', 'boolean'],
            'show_address' => ['required', 'boolean'],
            'show_tax' => ['required', 'boolean'],
            'auto_print' => ['required', 'boolean'],
        ];
    }
}
