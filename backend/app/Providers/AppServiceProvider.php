<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Contracts\TranslationProvider;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StorefrontSetting;
use App\Observers\CatalogCacheObserver;
use App\Payment\FakeGateway;
use App\Services\GoogleCloudTranslationProvider;
use App\Services\PlanGate;
use App\Support\E2eDatabaseLease;
use App\Support\PostgresConnectionProfile;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TranslationProvider::class, GoogleCloudTranslationProvider::class);

        // Request-scoped so its per-store capability map is memoized within a request.
        $this->app->singleton(PlanGate::class);

        // Gateway contract — swap for a real provider (Dibsy, MyFatoorah, Tap) in production.
        $this->app->bind(PaymentGateway::class, FakeGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('e2e')) {
            $phase = (string) env('P00_E2E_PHASE');
            if (in_array($phase, ['provisioning-migrate', 'provisioning-seed', 'active'], true)) {
                $profile = PostgresConnectionProfile::fromUrl((string) env('P00_E2E_DB_URL'));
                DB::setReconnector(
                    static fn (Connection $connection): never => throw new RuntimeException('P00_RECONNECT_REFUSED'),
                );
                $connection = DB::connection('e2e');
                $profile->assertLaravelConfiguration($connection, 'e2e');
                $pdo = $connection->getPdo();
                E2eDatabaseLease::assertBootConnection(
                    $pdo,
                    (string) env('P00_E2E_DATABASE'),
                    (string) env('P00_E2E_ROLE'),
                    (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
                    (string) env('P00_E2E_ACTIVATION_NONCE_SHA256'),
                    (string) env('P00_E2E_FIXTURE_CONTRACT_SHA256'),
                    $phase,
                );
                PostgresConnectionProfile::sealVerifiedPdo($connection, $pdo);
            } elseif ($phase !== 'supervisor') {
                throw new RuntimeException('Unrecognized E2E boot phase.');
            }
        }

        // Invalidate the public storefront cache when catalog data changes.
        Product::observe(CatalogCacheObserver::class);
        Category::observe(CatalogCacheObserver::class);
        StorefrontSetting::observe(CatalogCacheObserver::class);
        Store::observe(CatalogCacheObserver::class);
    }
}
