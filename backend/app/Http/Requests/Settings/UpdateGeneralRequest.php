<?php

namespace App\Http\Requests\Settings;

use App\Enums\Language;
use Illuminate\Validation\Rule;

class UpdateGeneralRequest extends SettingsRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'language' => ['required', Rule::in(Language::values())],
        ];
    }
}
