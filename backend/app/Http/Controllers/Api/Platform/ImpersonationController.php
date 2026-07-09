<?php

namespace App\Http\Controllers\Api\Platform;

use App\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /platform/stores/{store}/impersonate — mints a short-lived token that
 * signs the super-admin into the store's owner session for support/debugging.
 * This reads private tenant data, so entry is always audit-logged. The client
 * stashes the admin's own token and restores it on exit (no server state).
 */
class ImpersonationController extends Controller
{
    public function store(Request $request, Store $store): JsonResponse
    {
        $ownerMembership = $store->memberships()
            ->where('role', StaffRole::OWNER->value)
            ->where('is_active', true)
            ->with('user')
            ->orderBy('id')
            ->first();

        abort_if($ownerMembership?->user === null, 422, 'This store has no active owner to impersonate.');

        $owner = $ownerMembership->user;

        // Short-lived token on the owner account; existing middleware then resolves
        // the owner's membership and grants full back-office access to this store.
        $token = $owner->createToken(
            'impersonation:admin:'.$request->user()->id,
            ['*'],
            now()->addHour(),
        )->plainTextToken;

        PlatformAuditLog::record(
            $request->user(),
            'store.impersonate',
            $store,
            $store->name,
            ['owner_user_id' => $owner->id, 'owner_email' => $owner->email],
            $request->ip(),
        );

        return response()->json([
            'data' => [
                'token' => $token,
                'store' => ['id' => $store->id, 'name' => $store->name],
                'acting_as' => ['id' => $owner->id, 'name' => $owner->name, 'email' => $owner->email],
                'expires_in_minutes' => 60,
            ],
        ]);
    }
}
