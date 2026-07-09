<?php

namespace App\Actions;

use App\Enums\PlanFeature;
use App\Enums\StaffRole;
use App\Mail\StaffInvitationMail;
use App\Models\StaffInvitation;
use App\Models\Store;
use App\Models\User;
use App\Services\PlanGate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteStaffAction
{
    public function __construct(private readonly PlanGate $plans) {}

    /** @param array{name:string,email:string,role:string} $data */
    public function execute(Store $store, User $inviter, array $data): StaffInvitation
    {
        // A seat = an active member or a pending invite. Block the invite that
        // would take the store past its plan's STAFF_SEATS cap.
        $seatsInUse = $store->memberships()->where('is_active', true)->count()
            + $store->staffInvitations()->pending()->count();
        $this->plans->ensureWithinLimit($store, PlanFeature::STAFF_SEATS, $seatsInUse);

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
