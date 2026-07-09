<?php

namespace App\Http\Resources;

use App\Models\StoreUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A store member row for the Users page. `id` is the user id so the frontend can
 * PATCH/DELETE /staff/{user}. Pending invitations are emitted separately by the
 * controller with invitation_pending=true.
 *
 * @mixin StoreUser
 */
class StaffMemberResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'role' => $this->role->value,
            'is_active' => (bool) $this->is_active,
            'joined_at' => $this->joined_at?->toDateString(),
            'invitation_pending' => false,
        ];
    }
}
