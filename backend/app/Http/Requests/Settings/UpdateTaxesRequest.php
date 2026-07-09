<?php

namespace App\Http\Requests\Settings;

class UpdateTaxesRequest extends SettingsRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'charge_sales_tax' => ['required', 'boolean'],
            'tax_rate' => ['required_if:charge_sales_tax,true', 'nullable', 'numeric', 'min:0', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:64'],
            'tax_included_in_price' => ['required', 'boolean'],
        ];
    }
}
