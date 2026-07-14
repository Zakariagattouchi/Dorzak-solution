<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Services\ProductService;
use App\Support\StoreContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class E2ESeeder extends Seeder
{
    public const CONTRACT_JSON = '{"country":"Qatar","currency":"QAR","email":"owner@e2e.dorzak.test","plan":"PRO","productSku":"E2E-HOODIE","schemaVersion":1}';

    public function run(): void
    {
        $this->call(PlanSeeder::class);

        $owner = User::create([
            'name' => 'Dorzak E2E Owner',
            'email' => 'owner@e2e.dorzak.test',
            'password' => Hash::make('e2e-password'),
        ]);
        $store = Store::create([
            'name' => 'Dorzak E2E Merchant',
            'tagline' => 'Deterministic browser fixture',
            'owner_name' => $owner->name,
            'email' => $owner->email,
            'country' => 'Qatar',
            'timezone' => 'Asia/Qatar',
            'language' => 'en',
            'currency' => 'QAR',
            'symbol_placement' => 'BEFORE',
            'charge_sales_tax' => false,
            'tax_rate' => 0,
        ]);
        $store->initializeSettings();
        $store->subscription->update([
            'plan_id' => Plan::where('code', 'PRO')->value('id'),
            'status' => 'ACTIVE',
        ]);
        StoreUser::create([
            'store_id' => $store->id,
            'user_id' => $owner->id,
            'role' => StaffRole::OWNER,
            'is_active' => true,
            'joined_at' => now(),
        ]);
        app(StoreContext::class)->setStore($store);
        $category = Category::create(['name' => 'Apparel', 'color' => '#17201e']);

        app(ProductService::class)->create([
            'name' => 'Dorzak Signature Cotton Hoodie',
            'price' => 49.99,
            'cost' => 18,
            'category_id' => $category->id,
            'sku' => 'E2E-HOODIE',
            'taxable' => false,
            'track_stock' => true,
            'variant_groups' => [
                ['id' => 'size', 'name' => 'Size', 'required' => true, 'options' => [['id' => 'small', 'name' => 'Small']]],
                ['id' => 'color', 'name' => 'Color', 'required' => true, 'options' => [['id' => 'black', 'name' => 'Black']]],
            ],
            'variants' => [[
                'name' => 'Small / Black',
                'option_values' => ['size' => 'small', 'color' => 'black'],
                'price' => 49.99,
                'stock' => 10,
                'sku' => 'E2E-HOODIE-S-BLK',
                'is_active' => true,
            ]],
        ], $owner);
    }
}
