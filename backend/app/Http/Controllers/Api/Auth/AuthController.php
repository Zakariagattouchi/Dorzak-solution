<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthSessionResource;
use App\Models\User;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function login(LoginRequest $request): AuthSessionResource
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

        if ($membership === null) {
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

    /** GET /auth/me — current session/store/role/abilities. */
    public function me(Request $request): AuthSessionResource
    {
        $membership = app(StoreContext::class)->membership();
        $membership->setRelation('user', $request->user());

        return new AuthSessionResource($membership);
    }

    /** POST /auth/logout — revoke current token and/or invalidate the session. */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        // Personal access token (bearer) — revoke just this one.
        if ($token !== null && ! $token instanceof TransientToken) {
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
