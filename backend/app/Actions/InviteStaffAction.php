<?php

namespace App\Actions;

use App\Enums\StaffRole;
use App\Mail\StaffInvitationMail;
use App\Models\StaffInvitation;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteStaffAction
{
    /** @param array{name:string,email:string,role:string} $data */
    public function execute(Store $store, User $inviter, array $data): StaffInvitation
    {
        $invitation = $store->staffInvitations()->create([
            'invited_by' => $inviter->id,
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'role' => StaffRole::from($data['role']),
            'token' => Str::random(48),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->queue(new StaffInvitationMail($invitation));

        return $invitation;
    }
}
