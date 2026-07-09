<?php

namespace App\Services;

use App\Enums\StaffRole;
use App\Exceptions\DomainConflictException;
use App\Models\StoreUser;
use App\Models\User;

/**
 * Membership mutations with the owner-protection invariants (docs 03 §1, 05 Staff):
 * only an owner may touch an owner, and a store must always keep >= 1 active owner.
 */
class StaffService
{
    public function updateMember(StoreUser $membership, array $data, User $actor): StoreUser
    {
        $this->assertActorMayManage($membership, $actor);

        $newRole = isset($data['role']) ? StaffRole::from($data['role']) : $membership->role;
        $newActive = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $membership->isActive();

        // Guard the last active owner against demotion or deactivation.
        $losesOwner = $membership->role === StaffRole::OWNER
            && ($newRole !== StaffRole::OWNER || ! $newActive);

        if ($losesOwner && $this->isLastActiveOwner($membership)) {
            throw new DomainConflictException('LAST_OWNER', 'A store must keep at least one active owner.');
        }

        $membership->fill(['role' => $newRole, 'is_active' => $newActive])->save();

        if (! $newActive) {
            $this->revokeTokens($membership);
        }

        return $membership->fresh('user');
    }

    public function removeMember(StoreUser $membership, User $actor): void
    {
        $this->assertActorMayManage($membership, $actor);

        if ($membership->role === StaffRole::OWNER && $this->isLastActiveOwner($membership)) {
            throw new DomainConflictException('LAST_OWNER', 'A store must keep at least one active owner.');
        }

        $this->revokeTokens($membership);
        $membership->delete();
    }

    private function assertActorMayManage(StoreUser $membership, User $actor): void
    {
        // Only an owner may modify or remove another owner.
        if ($membership->role === StaffRole::OWNER && $actor->currentRole() !== StaffRole::OWNER) {
            abort(403, 'Only an owner can manage an owner.');
        }
    }

    private function isLastActiveOwner(StoreUser $membership): bool
    {
        return $membership->store->activeOwnerCount() <= 1;
    }

    private function revokeTokens(StoreUser $membership): void
    {
        $membership->user?->tokens()->delete();
    }
}
