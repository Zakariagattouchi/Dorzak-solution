<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthSessionResource;
use App\Http\Resources\PlatformSessionResource;
use App\Models\User;
use App\Support\ImpersonationToken;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\TransientToken;

class AuthController extends Controller
{
    /**
     * POST /auth/login — email + password. Establishes the SPA session cookie and,
     * when a device_name is supplied, also returns a bearer token. See docs 05.
     */
    public function login(LoginRequest $request): JsonResource
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $storeId = $request->hasHeader('X-Store-Id') ? (int) $request->header('X-Store-Id') : null;
        $membership = $user->currentMembership($storeId);

        // A store-less platform (super) admin gets a store-less session that the
        // SPA routes to the platform console instead of the merchant back office.
        if ($membership === null) {
            if ($user->is_platform_admin) {
                if ($request->hasSession()) {
                    Auth::guard('web')->login($user);
                    $request->session()->regenerate();
                }

                $token = isset($data['device_name']) ? $user->createToken($data['device_name'])->plainTextToken : null;

                return new PlatformSessionResource($user, $token);
            }

            abort(403, 'You do not belong to any store.');
        }

        if (! $membership->isActive()) {
            abort(response()->json([
                'message' => 'Your access to this store has been disabled.',
                'code' => 'ACCOUNT_DISABLED',
            ], 403));
        }

        $membership->setRelation('user', $user);

        // Stateful (cookie) login for the SPA.
        if ($request->hasSession()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
        }

        // Bind context so the resource can read abilities immediately.
        app(StoreContext::class)->setMembership($membership);

        $token = isset($data['device_name'])
            ? $user->createToken($data['device_name'])->plainTextToken
            : null;

        return new AuthSessionResource($membership, $token);
    }

    /** GET /auth/me — current session/store/role/abilities (or platform session). */
    public function me(Request $request): JsonResource
    {
        $membership = app(StoreContext::class)->membership();

        if ($membership !== null) {
            if (! $membership->isActive() && ! $request->user()->is_platform_admin) {
                abort(response()->json([
                    'message' => 'Your access to this store has been disabled.',
                    'code' => 'ACCOUNT_DISABLED',
                ], 403));
            }

            $membership->setRelation('user', $request->user());

            return new AuthSessionResource($membership);
        }

        if ($request->user()->is_platform_admin) {
            return new PlatformSessionResource($request->user());
        }

        abort(403, 'You do not belong to any store.');
    }

    /** POST /auth/logout — revoke current token and/or invalidate the session. */
    public function logout(Request $request): JsonResponse
    {
        $presentedToken = ImpersonationToken::fromRequest($request);
        $presentedToken?->delete();

        $token = $request->user()?->currentAccessToken();

        // Personal access token (bearer) — revoke just this one.
        if ($token !== null
            && ! $token instanceof TransientToken
            && ($presentedToken === null || ! $token->is($presentedToken))) {
            $token->delete();
        }

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(status: 204);
    }
}
