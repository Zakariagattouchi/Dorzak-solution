<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Validator;

class UpdatePaymentsRequest extends SettingsRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cash' => ['required', 'boolean'],
            'card' => ['required', 'boolean'],
            'transfer' => ['required', 'boolean'],
            'whatsapp' => ['required', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:255'],
            'whatsapp_phone' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            // POS must have at least one in-person method; WhatsApp is online-only.
            if (! $this->boolean('cash') && ! $this->boolean('card') && ! $this->boolean('transfer')) {
                $validator->errors()->add('cash', 'At least one POS payment method (cash, card, or transfer) must be enabled.');
            }
        });
    }
}
