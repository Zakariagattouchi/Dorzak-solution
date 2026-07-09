<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class UpdateBusinessRequest extends SettingsRequest
{
    /** Countries offered by the frontend Business Info tab. */
    public const COUNTRIES = [
        'United States', 'United Kingdom', 'Canada', 'Brazil', 'Mexico', 'Panama',
        'Colombia', 'Argentina', 'Australia', 'Germany', 'France', 'Qatar',
    ];

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'owner_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:190'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', Rule::in(self::COUNTRIES)],
        ];
    }
}
