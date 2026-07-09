<?php

namespace App\Http\Middleware;

use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
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
}
