<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\RegisterStoreAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthSessionResource;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegisteredStoreController extends Controller
{
    /**
     * POST /auth/register — SaaS signup. Creates user + store + OWNER membership
     * (and, from TP-02, default settings). Logs in and returns the session payload.
     */
    public function store(RegisterRequest $request, RegisterStoreAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        $user = $result['user'];
        $membership = $result['membership'];

        if ($request->hasSession()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
        }

        app(StoreContext::class)->setMembership($membership);

        $token = $request->filled('device_name')
            ? $user->createToken($request->string('device_name')->toString())->plainTextToken
            : null;

        return (new AuthSessionResource($membership, $token))
            ->response()
            ->setStatusCode(201);
    }
}
