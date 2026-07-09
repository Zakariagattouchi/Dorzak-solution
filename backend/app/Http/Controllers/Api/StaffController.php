<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateStaffMemberRequest;
use App\Http\Resources\StaffMemberResource;
use App\Models\StoreUser;
use App\Services\StaffService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;

class StaffController extends Controller
{
    public function __construct(
        private readonly StaffService $staff,
        private readonly StoreContext $context,
    ) {}

    /** GET /staff — active/inactive members plus pending invitations. */
    public function index(): JsonResponse
    {
        abort_unless(request()->user()->can('staff.view'), 403);

        $store = $this->context->store();

        $members = $store->memberships()->with('user')->get()
            ->map(fn (StoreUser $m) => (new StaffMemberResource($m))->resolve());

        $pending = $store->staffInvitations()->pending()->latest()->get()
            ->map(fn ($invite) => [
                'id' => "invite_{$invite->id}",
                'invitation_id' => $invite->id,
                'name' => $invite->name,
                'email' => $invite->email,
                'role' => $invite->role->value,
                'is_active' => false,
                'joined_at' => null,
                'invitation_pending' => true,
            ]);

        return response()->json(['data' => $members->concat($pending)->values()]);
    }

    /** PATCH /staff/{user} — change role and/or active flag. */
    public function update(UpdateStaffMemberRequest $request, int $user): StaffMemberResource
    {
        $membership = $this->membershipForUser($user);
        $updated = $this->staff->updateMember($membership, $request->validated(), $request->user());

        return new StaffMemberResource($updated);
    }

    /** DELETE /staff/{user} — detach the member from the store. */
    public function destroy(int $user): JsonResponse
    {
        $membership = $this->membershipForUser($user);
        $this->staff->removeMember($membership, request()->user());

        return response()->json(status: 204);
    }

    private function membershipForUser(int $userId): StoreUser
    {
        return $this->context->store()
            ->memberships()
            ->with(['user', 'store'])
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}
