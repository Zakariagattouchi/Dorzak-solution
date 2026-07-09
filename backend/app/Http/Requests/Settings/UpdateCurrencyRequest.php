<?php

namespace App\Http\Requests\Settings;

use App\Enums\Currency;
use App\Enums\SymbolPlacement;
use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends SettingsRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'currency' => ['required', Rule::in(Currency::values())],
            'symbol_placement' => ['required', Rule::in(SymbolPlacement::values())],
        ];
    }
}
