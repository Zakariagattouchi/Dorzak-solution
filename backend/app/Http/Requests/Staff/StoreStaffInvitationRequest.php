<?php

namespace App\Http\Requests\Staff;

use App\Enums\StaffRole;
use App\Support\StoreContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStaffInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('staff.manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(array_map(fn (StaffRole $r) => $r->value, StaffRole::assignable()))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $store = app(StoreContext::class)->store();
            $email = strtolower(trim((string) $this->input('email')));

            $alreadyMember = $store->memberships()
                ->whereHas('user', fn ($q) => $q->where('email', $email))
                ->exists();

            if ($alreadyMember) {
                $validator->errors()->add('email', 'That person is already a member of this store.');

                return;
            }

            $pending = $store->staffInvitations()->pending()->where('email', $email)->exists();

            if ($pending) {
                $validator->errors()->add('email', 'An invitation is already pending for that email.');
            }
        });
    }
}
