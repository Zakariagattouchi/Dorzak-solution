<?php

namespace App\Http\Controllers\Api\Platform;

use App\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Cross-tenant user administration for platform super-admins. Lists every user
 * on the platform with their memberships, and lets an admin grant/revoke admin,
 * disable access, or force a password reset. Every mutation is audit-logged.
 */
class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with(['memberships.store:id,name'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->input('filter') === 'admins', fn ($q) => $q->where('is_platform_admin', true))
            ->orderBy('id')
            ->paginate(50);

        return response()->json([
            'data' => $users->getCollection()->map(fn (User $u) => $this->userShape($u)),
            'meta' => ['total' => $users->total(), 'per_page' => $users->perPage(), 'current_page' => $users->currentPage()],
        ]);
    }

    public function grantAdmin(Request $request, User $user): JsonResponse
    {
        $user->forceFill(['is_platform_admin' => true])->save();
        $this->audit($request, 'user.grant_admin', $user);

        return response()->json(['data' => $this->userShape($user->load('memberships.store:id,name'))]);
    }

    public function revokeAdmin(Request $request, User $user): JsonResponse
    {
        // Never leave the platform with no super-admin.
        if (User::where('is_platform_admin', true)->count() <= 1 && $user->is_platform_admin) {
            abort(422, 'Cannot revoke the last remaining platform admin.');
        }

        $user->forceFill(['is_platform_admin' => false])->save();
        $this->audit($request, 'user.revoke_admin', $user);

        return response()->json(['data' => $this->userShape($user->load('memberships.store:id,name'))]);
    }

    /** Enable or disable this user across ALL their store memberships. */
    public function setActive(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['is_active' => 'required|boolean']);

        if (! $data['is_active'] && $user->id === $request->user()->id) {
            abort(422, 'You cannot disable your own account.');
        }

        $user->memberships()->update(['is_active' => $data['is_active']]);
        $this->audit($request, $data['is_active'] ? 'user.activate' : 'user.deactivate', $user);

        return response()->json(['data' => $this->userShape($user->load('memberships.store:id,name'))]);
    }

    /** Force a new random password; returned once so the admin can relay it. */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $temporary = Str::password(14);
        $user->forceFill(['password' => $temporary])->save(); // hashed by cast
        $user->tokens()->delete(); // invalidate existing sessions
        $this->audit($request, 'user.reset_password', $user);

        return response()->json(['data' => ['temporary_password' => $temporary]]);
    }

    private function audit(Request $request, string $action, User $user): void
    {
        PlatformAuditLog::record(
            $request->user(),
            $action,
            $user,
            $user->email,
            [],
            $request->ip(),
        );
    }

    private function userShape(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_platform_admin' => (bool) $user->is_platform_admin,
            'is_active' => $user->memberships->isEmpty() || $user->memberships->contains(fn ($m) => $m->is_active),
            'memberships' => $user->memberships->map(fn ($m) => [
                'store_id' => $m->store_id,
                'store_name' => $m->store?->name,
                'role' => $m->role instanceof StaffRole ? $m->role->value : $m->role,
                'is_active' => (bool) $m->is_active,
            ])->values(),
        ];
    }
}
