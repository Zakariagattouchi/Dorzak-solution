<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Contracts\TranslationProvider;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontSetting;
use App\Observers\CatalogCacheObserver;
use App\Payment\FakeGateway;
use App\Services\GoogleCloudTranslationProvider;
use App\Services\PlanGate;
use Illuminate\Support\ServiceProvider;

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
        // Invalidate the public storefront cache when catalog data changes.
        Product::observe(CatalogCacheObserver::class);
        Category::observe(CatalogCacheObserver::class);
        StorefrontSetting::observe(CatalogCacheObserver::class);
    }
}
