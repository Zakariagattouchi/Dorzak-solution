<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\ImpersonationToken;
use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the authenticated user's current store membership and binds it into the
 * request-scoped StoreContext (drives tenant scoping + ability gates). Population
 * only — the active-membership guard lives in EnsureStoreMember. See docs 06 §1–2.
 */
class SetStoreContext
{
    public function __construct(private readonly StoreContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $presentedToken = ImpersonationToken::fromRequest($request);

        if ($presentedToken !== null) {
            if (! $this->tokenIsCurrent($presentedToken)) {
                return $this->rejectImpersonationOrContinueLogout($request, $next, $presentedToken);
            }

            $tokenable = $presentedToken->tokenable;

            if (! $tokenable instanceof User) {
                abort(401);
            }

            $user = $tokenable->withAccessToken($presentedToken);
            Auth::guard('sanctum')->setUser($user);
            $request->setUserResolver(static fn (?string $guard = null): User => $user);

            if (ImpersonationToken::isImpersonation($presentedToken)) {
                $storeId = ImpersonationToken::boundStoreId($presentedToken);
                $membership = $storeId === null ? null : $user->currentMembership($storeId);

                if ($membership === null || ! $membership->isActive()) {
                    return $this->rejectImpersonationOrContinueLogout($request, $next, $presentedToken);
                }

                $this->context->setMembership($membership);

                return $next($request);
            }
        }

        $user = $request->user();

        // Always resolve fresh for this request. (The scoped StoreContext is not
        // guaranteed to be flushed between sequential requests in a single test,
        // so we set — or clear — it every time rather than guarding on hasStore.)
        if ($user !== null) {
            $storeId = $request->hasHeader('X-Store-Id')
                ? (int) $request->header('X-Store-Id')
                : null;

            $this->context->setMembership($user->currentMembership($storeId));
        } else {
            $this->context->setMembership(null);
        }

        return $next($request);
    }

    private function tokenIsCurrent(PersonalAccessToken $token): bool
    {
        $expiration = config('sanctum.expiration');
        $expiredByAge = is_numeric($expiration)
            && (int) $expiration > 0
            && ($token->created_at === null || ! $token->created_at->gt(now()->subMinutes((int) $expiration)));

        return ! $expiredByAge
            && ($token->expires_at === null || ! $token->expires_at->isPast());
    }

    private function rejectImpersonationOrContinueLogout(
        Request $request,
        Closure $next,
        PersonalAccessToken $token,
    ): Response {
        if (ImpersonationToken::isImpersonation($token)
            && $request->isMethod('POST')
            && $request->is('api/v1/auth/logout')) {
            $this->context->setMembership(null);

            return $next($request);
        }

        if (ImpersonationToken::isImpersonation($token)) {
            abort(response()->json([
                'message' => 'The impersonation token has an invalid store context.',
                'code' => 'INVALID_IMPERSONATION_CONTEXT',
            ], 403));
        }

        abort(401);
    }
}
