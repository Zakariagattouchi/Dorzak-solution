<?php

namespace App\Http\Middleware;

use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards every authed /api/v1 route: the current-store membership (bound by
 * SetStoreContext) must exist and be active. Distinguishes "no store" from a
 * deliberately disabled member (403 ACCOUNT_DISABLED). See docs 06 §1.
 */
class EnsureStoreMember
{
    public function __construct(private readonly StoreContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null) {
            abort(401);
        }

        $membership = $this->context->membership();

        if ($membership === null) {
            abort(403, 'You do not belong to any store.');
        }

        if (! $membership->isActive()) {
            abort(response()->json([
                'message' => 'Your access to this store has been disabled.',
                'code' => 'ACCOUNT_DISABLED',
            ], 403));
        }

        return $next($request);
    }
}
