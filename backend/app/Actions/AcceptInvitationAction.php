<?php

namespace App\Actions;

use App\Models\StaffInvitation;
use App\Models\StoreUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AcceptInvitationAction
{
    /**
     * Accept a pending invitation: create-or-attach the user and add the store
     * membership in the invited role. Returns the membership with relations set.
     *
     * @param  array{password:string,name?:string}  $data
     */
    public function execute(StaffInvitation $invitation, array $data): StoreUser
    {
        if (! $invitation->isPending()) {
            abort(409, 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            abort(410, 'This invitation has expired.');
        }

        return DB::transaction(function () use ($invitation, $data): StoreUser {
            $user = User::firstOrNew(['email' => $invitation->email]);

            if (! $user->exists) {
                $user->fill([
                    'name' => $data['name'] ?? $invitation->name,
                    'password' => Hash::make($data['password']),
                ])->save();
            }

            $membership = StoreUser::updateOrCreate(
                ['store_id' => $invitation->store_id, 'user_id' => $user->id],
                ['role' => $invitation->role, 'is_active' => true, 'joined_at' => now()],
            );

            $invitation->forceFill(['accepted_at' => now()])->save();

            $membership->setRelation('user', $user);
            $membership->setRelation('store', $invitation->store);

            return $membership;
        });
    }
}
