<?php

namespace App\Http\Requests\Settings;

class UpdateIntegrationsRequest extends SettingsRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'facebook_pixel_id' => ['nullable', 'string', 'regex:/^\d{5,20}$/'],
            'google_analytics_id' => ['nullable', 'string', 'regex:/^G-[A-Z0-9]{4,}$/'],
            'facebook_connected' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'facebook_pixel_id.regex' => 'The Facebook Pixel ID must be 5–20 digits.',
            'google_analytics_id.regex' => 'The Google Analytics ID must look like G-XXXXXXXX.',
        ];
    }
}
