<?php

namespace App\Http\Requests\Staff;

use App\Enums\StaffRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('staff.manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'role' => ['sometimes', Rule::enum(StaffRole::class)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->has('role') && ! $this->has('is_active')) {
                $validator->errors()->add('role', 'Provide a role or is_active to update.');
            }
        });
    }
}
