<?php

namespace App\Providers;

use App\Models\User;
use App\Support\RoleMatrix;
use App\Support\StoreContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One StoreContext per request; populated by SetStoreContext middleware.
        $this->app->scoped(StoreContext::class, fn () => new StoreContext);
    }

    public function boot(): void
    {
        // Define one gate per ability, resolving the user's role in the current store.
        foreach (RoleMatrix::abilities() as $ability) {
            Gate::define($ability, static function (User $user) use ($ability): bool {
                return RoleMatrix::allows($user->currentRole(), $ability);
            });
        }

        // Login throttle: 5 attempts/min keyed by email + IP (docs 12 §1).
        RateLimiter::for('login', static function (Request $request): Limit {
            $key = strtolower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });
    }
}
