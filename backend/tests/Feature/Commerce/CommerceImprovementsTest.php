<?php

namespace Tests\Feature\Commerce;

use App\Contracts\TranslationProvider;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommerceImprovementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_arabic_content_and_combinations_round_trip(): void
    {
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();
        $category = $store->categories()->create(['name' => 'Clothing', 'color' => '#123456']);

        $response = $this->actingAsMember($owner)->postJson('/api/v1/products', [
            'name' => 'T-Shirt',
            'name_ar' => 'قميص',
            'description_ar' => 'قميص قطني',
            'price' => 50,
            'category_id' => $category->id,
            'variant_groups' => [[
                'id' => 'size', 'name' => 'Size', 'required' => true,
                'options' => [['id' => 'large', 'name' => 'Large']],
            ]],
            'variants' => [[
                'name' => 'Large', 'option_values' => ['size' => 'large'],
                'price' => 55, 'stock' => 4, 'is_active' => true,
            ]],
        ])->assertCreated();

        $response->assertJsonPath('data.name_ar', 'قميص')
            ->assertJsonPath('data.variant_groups.0.id', 'size')
            ->assertJsonPath('data.variants.0.option_values.size', 'large');
    }

    public function test_translation_endpoint_uses_configured_provider(): void
    {
        ['user' => $owner] = $this->createStoreWithOwner();
        $this->app->bind(TranslationProvider::class, fn () => new class implements TranslationProvider
        {
            public function translateToArabic(array $texts): array
            {
                return ['قهوة', 'قهوة طازجة'];
            }
        });

        $this->actingAsMember($owner)->postJson('/api/v1/products/translate-arabic', [
            'name' => 'Coffee', 'description' => 'Fresh coffee',
        ])->assertOk()
            ->assertJsonPath('data.name_ar', 'قهوة')
            ->assertJsonPath('data.description_ar', 'قهوة طازجة');
    }

    public function test_category_photo_upload_is_exposed_to_admin_and_public_catalog(): void
    {
        Storage::fake('public');
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner();
        $store->storefrontSetting->update(['online_store_enabled' => true, 'slug' => 'photo-shop']);
        $category = $store->categories()->create(['name' => 'Desserts', 'color' => '#7c3aed']);

        $this->actingAsMember($owner)->post("/api/v1/categories/{$category->id}/image", [
            'file' => UploadedFile::fake()->image('desserts.webp'),
        ])->assertOk();

        $path = $category->fresh()->image_path;
        Storage::disk('public')->assertExists($path);
        $this->getJson('/api/public/stores/photo-shop/catalog')
            ->assertOk()
            ->assertJsonPath('data.categories.0.name', 'Desserts')
            ->assertJsonPath('data.categories.0.image_url', 'http://localhost'.Storage::disk('public')->url($path));
    }

    public function test_fawran_order_stores_location_and_private_payment_proof(): void
    {
        Storage::fake('local');
        ['user' => $owner, 'store' => $store] = $this->createStoreWithOwner(['currency' => 'QAR']);
        $store->storefrontSetting->update([
            'online_store_enabled' => true, 'slug' => 'test-shop', 'fawran_enabled' => true,
        ]);
        $product = Product::factory()->for($store)->create(['price' => 25, 'stock' => 10]);

        $response = $this->post('/api/public/stores/test-shop/orders', [
            'customer' => [
                'name' => 'Noor', 'phone' => '+97450000000', 'address' => 'West Bay',
                'city' => 'Doha', 'latitude' => 25.328, 'longitude' => 51.531,
            ],
            'fulfillment' => 'delivery',
            'payment_method' => 'FAWRAN',
            'payment_reference' => 'FWR-123',
            'payment_proof' => UploadedFile::fake()->image('receipt.jpg'),
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();

        $response->assertJsonPath('data.status', 'CONFIRMING')
            ->assertJsonPath('data.payment_status', 'PENDING_VERIFICATION');
        $this->assertDatabaseHas('orders', [
            'currency_code' => 'QAR', 'payment_reference' => 'FWR-123',
            'delivery_city' => 'Doha',
        ]);
        $order = $store->orders()->firstOrFail();
        Storage::disk('local')->assertExists($order->payment_proof_path);
        $this->actingAsMember($owner)
            ->get("/api/v1/orders/{$order->id}/payment-proof")
            ->assertOk();
    }
}
