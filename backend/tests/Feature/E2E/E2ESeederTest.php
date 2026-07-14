<?php

namespace Tests\Feature\E2E;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\E2ESeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class E2ESeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_fixture_is_one_repeatable_qatar_merchant(): void
    {
        $this->seed(E2ESeeder::class);

        $store = Store::sole();
        self::assertSame('Dorzak E2E Merchant', $store->name);
        self::assertSame('Qatar', $store->country);
        self::assertSame('QAR', $store->currency);
        self::assertSame('BEFORE', $store->symbol_placement);
        self::assertSame('PRO', $store->subscription->plan->code);
        self::assertSame('owner@e2e.dorzak.test', User::sole()->email);

        $product = Product::with('variants')->sole();
        self::assertSame('Dorzak Signature Cotton Hoodie', $product->name);
        self::assertSame('49.99', (string) $product->price);
        self::assertCount(1, $product->variants);
        self::assertSame(['size' => 'small', 'color' => 'black'], $product->variants->sole()->option_values);
    }
}
