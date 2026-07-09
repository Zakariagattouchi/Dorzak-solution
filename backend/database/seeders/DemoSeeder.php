<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Support\StoreContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Reproduces src/data/mockData.ts so the React app renders identically against the
 * real API. This is the acceptance harness (docs 10). Order numbers are the real
 * per-store sequence (ORD-1000…), not the mock's random ids — parity is structural.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'merchant@dorzak.com'],
            ['name' => 'Barsha Admin', 'password' => Hash::make('password')],
        );

        $store = Store::firstOrCreate(
            ['name' => 'Dorzak Merchant'],
            [
                'tagline' => 'Commerce made simple',
                'owner_name' => 'Barsha Admin',
                'email' => 'merchant@dorzak.com',
                'phone' => '+1 (555) 234-5678',
                'whatsapp' => '+1 (555) 234-5678',
                'address' => '742 Evergreen Terrace',
                'city' => 'Doha', 'state' => 'DO', 'zip_code' => '',
                'country' => 'Qatar', 'currency' => 'QAR', 'symbol_placement' => 'BEFORE',
                'charge_sales_tax' => true, 'tax_rate' => 8.5, 'tax_id' => 'US-991827364',
            ],
        );

        $store->initializeSettings();
        $store->subscription->update([
            'plan_id' => Plan::where('code', 'PRO')->value('id'),
            'status' => 'ACTIVE', 'price' => 99, 'renews_at' => '2027-07-05',
        ]);
        $store->storefrontSetting->update([
            'online_store_enabled' => true, 'slug' => 'dorzak-merchant',
            'bio' => 'Welcome to our official online shop!',
            'accent_color' => '#1890ff', 'secondary_color' => '#373f4e',
            'allow_delivery' => true, 'allow_pickup' => true, 'allow_dine_in' => true, 'dine_in_table_count' => 12,
            'delivery_fee' => 5, 'free_delivery_threshold' => 50, 'min_order_amount' => 10,
            'product_card_layout' => 'vertical', 'show_store_header' => true, 'show_store_gradient' => true,
            'navbar_color' => '#17201e',
        ]);
        $store->receiptSetting->update([
            'header' => 'Thank you for supporting our local business!',
            'footer' => 'Returns accepted within 30 days with receipt.',
        ]);

        $this->membership($store, $owner, StaffRole::OWNER);
        $this->membership($store, User::firstOrCreate(['email' => 'alex@example.com'], ['name' => 'Alex Cashier', 'password' => Hash::make('password')]), StaffRole::CASHIER);
        $this->membership($store, User::firstOrCreate(['email' => 'maria@example.com'], ['name' => 'Maria Manager', 'password' => Hash::make('password')]), StaffRole::MANAGER);

        // Scope subsequent catalog/order writes to the demo store.
        app(StoreContext::class)->setStore($store);

        if ($store->products()->exists()) {
            return; // already seeded
        }

        $cats = collect([
            ['Apparel & Fashion', '#3b82f6', 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=600'],
            ['Electronics & Tech', '#10b981', 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600'],
            ['Coffee & Beverages', '#f59e0b', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600'],
            ['Accessories', '#ec4899', 'https://images.unsplash.com/photo-1523779917675-b6ed3a42a561?w=600'],
            ['Home & Office', '#8b5cf6', 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=600'],
        ])->mapWithKeys(fn ($c) => [$c[0] => Category::create(['name' => $c[0], 'color' => $c[1], 'image_path' => $c[2]])]);

        $products = app(ProductService::class);

        $hoodie = $products->create([
            'name' => 'Dorzak Signature Cotton Hoodie', 'price' => 49.99, 'cost' => 18,
            'category_id' => $cats['Apparel & Fashion']->id, 'sku' => 'HOOD-001', 'taxable' => true,
            'image_url' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800',
            'variants' => [
                ['name' => 'Small / Black', 'price' => 49.99, 'stock' => 15, 'sku' => 'HOOD-S-BLK'],
                ['name' => 'Medium / Black', 'price' => 49.99, 'stock' => 20, 'sku' => 'HOOD-M-BLK'],
                ['name' => 'Large / Black', 'price' => 49.99, 'stock' => 10, 'sku' => 'HOOD-L-BLK'],
            ],
        ], $owner);

        $earbuds = $products->create(['name' => 'Wireless Noise-Canceling Earbuds', 'price' => 89.95, 'cost' => 35, 'category_id' => $cats['Electronics & Tech']->id, 'sku' => 'EAR-900', 'stock' => 22, 'min_stock' => 5, 'image_url' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800'], $owner);
        $coldBrew = $products->create(['name' => 'Artisan Cold Brew Coffee (750ml)', 'price' => 8.50, 'cost' => 2.20, 'category_id' => $cats['Coffee & Beverages']->id, 'sku' => 'COFF-01', 'unit' => 'bottle', 'stock' => 120, 'min_stock' => 20, 'taxable' => false, 'image_url' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=800'], $owner);
        $cardholder = $products->create(['name' => 'Minimalist Leather Cardholder', 'price' => 29, 'cost' => 8.50, 'category_id' => $cats['Accessories']->id, 'sku' => 'LTHR-CARD', 'stock' => 35, 'min_stock' => 8, 'image_url' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=800'], $owner);
        $deskMat = $products->create(['name' => 'Ergonomic Desk Mat', 'price' => 34.50, 'cost' => 11, 'category_id' => $cats['Home & Office']->id, 'sku' => 'MAT-002', 'stock' => 18, 'min_stock' => 5, 'image_url' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800'], $owner);
        $products->create(['name' => 'Stainless Steel Water Bottle (1L)', 'price' => 24.99, 'cost' => 6, 'category_id' => $cats['Accessories']->id, 'sku' => 'BTL-1000', 'stock' => 60, 'min_stock' => 15, 'image_url' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800'], $owner);

        $sarah = Customer::create(['name' => 'Sarah Jenkins', 'email' => 'sarah.j@example.com', 'phone' => '+1 555-0144', 'address' => '123 Market St', 'city' => 'San Francisco']);
        $david = Customer::create(['name' => 'David Miller', 'email' => 'dmiller@techcorp.io', 'phone' => '+1 555-0812', 'address' => '456 Tech Way', 'city' => 'San Jose']);
        $elena = Customer::create(['name' => 'Elena Rostova', 'email' => 'elena@designstudio.com', 'phone' => '+1 555-0923', 'address' => '789 Design Blvd', 'city' => 'Oakland']);
        Customer::create(['name' => 'Michael Vance', 'email' => 'mvance@startup.co', 'phone' => '+1 555-0377', 'address' => '101 Startup Alley', 'city' => 'San Francisco']);

        $orders = app(OrderService::class);
        $mediumBlack = $hoodie->variants->firstWhere('name', 'Medium / Black');

        $orders->create($store, [
            'items' => [
                ['product_id' => $hoodie->id, 'variant_id' => $mediumBlack->id, 'quantity' => 2],
                ['product_id' => $coldBrew->id, 'quantity' => 1],
            ],
            'customer_id' => $sarah->id, 'payment_method' => 'CARD', 'status' => 'COMPLETE',
            'discount' => 5, 'notes' => 'Customer requested gift receipt',
        ], $owner);

        $orders->create($store, [
            'items' => [['product_id' => $earbuds->id, 'quantity' => 1]],
            'customer_id' => $david->id, 'payment_method' => 'CASH', 'status' => 'COMPLETE',
        ], $owner);

        $orders->create($store, [
            'items' => [
                ['product_id' => $cardholder->id, 'quantity' => 1],
                ['product_id' => $deskMat->id, 'quantity' => 1],
            ],
            'customer_id' => $elena->id, 'payment_method' => 'TRANSFER', 'status' => 'CONFIRMING',
        ], $owner);
    }

    private function membership(Store $store, User $user, StaffRole $role): void
    {
        StoreUser::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $user->id],
            ['role' => $role, 'is_active' => true, 'joined_at' => now()],
        );
    }
}
