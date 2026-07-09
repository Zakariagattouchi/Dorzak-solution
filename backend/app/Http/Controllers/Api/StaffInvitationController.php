<?php

namespace App\Http\Controllers\Api;

use App\Actions\AcceptInvitationAction;
use App\Actions\InviteStaffAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AcceptInvitationRequest;
use App\Http\Requests\Staff\StoreStaffInvitationRequest;
use App\Http\Resources\AuthSessionResource;
use App\Mail\StaffInvitationMail;
use App\Models\StaffInvitation;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class StaffInvitationController extends Controller
{
    public function __construct(private readonly StoreContext $context) {}

    /** POST /staff/invitations — invite a new member (owner/manager). */
    public function store(StoreStaffInvitationRequest $request, InviteStaffAction $action): JsonResponse
    {
        $invitation = $action->execute($this->context->store(), $request->user(), $request->validated());

        return response()->json([
            'data' => [
                'id' => "invite_{$invitation->id}",
                'invitation_id' => $invitation->id,
                'name' => $invitation->name,
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'is_active' => false,
                'joined_at' => null,
                'invitation_pending' => true,
            ],
        ], 201);
    }

    /** POST /staff/invitations/{token}/accept — public; sets up the account + session. */
    public function accept(AcceptInvitationRequest $request, string $token, AcceptInvitationAction $action): JsonResponse
    {
        $invitation = StaffInvitation::where('token', $token)->firstOrFail();

        $membership = $action->execute($invitation, $request->validated());

        if ($request->hasSession()) {
            Auth::guard('web')->login($membership->user);
            $request->session()->regenerate();
        }

        $this->context->setMembership($membership);

        $device = $request->string('device_name')->toString();
        $token = $device !== '' ? $membership->user->createToken($device)->plainTextToken : null;

        return (new AuthSessionResource($membership, $token))->response()->setStatusCode(201);
    }

    /** POST /staff/invitations/{invitation}/resend */
    public function resend(int $invitation): JsonResponse
    {
        $invite = $this->pendingInvitation($invitation);
        $invite->update(['expires_at' => now()->addDays(7)]);
        Mail::to($invite->email)->queue(new StaffInvitationMail($invite));

        return response()->json(status: 202);
    }

    /** DELETE /staff/invitations/{invitation} */
    public function destroy(int $invitation): JsonResponse
    {
        $this->pendingInvitation($invitation)->delete();

        return response()->json(status: 204);
    }

    private function pendingInvitation(int $id): StaffInvitation
    {
        abort_unless(request()->user()->can('staff.manage'), 403);

        return $this->context->store()->staffInvitations()->pending()->findOrFail($id);
    }
}
