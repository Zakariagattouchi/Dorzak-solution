<?php

namespace Tests\Feature\Settings;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'online_store_enabled' => true,
            'store_slug' => 'dorzak-merchant',
            'store_bio' => 'Welcome to our shop',
            'banner_url' => 'https://example.com/banner.jpg',
            'logo_url' => 'https://example.com/logo.png',
            'accent_color' => '#1890ff',
            'allow_delivery' => true,
            'allow_pickup' => true,
            'allow_dine_in' => true,
            'dine_in_table_count' => 12,
            'delivery_fee' => 5,
            'free_delivery_threshold' => 50,
            'min_order_amount' => 10,
            'whatsapp_ordering_enabled' => true,
            'show_out_of_stock_online' => true,
        ], $overrides);
    }

    public function test_storefront_saves_and_exposes_public_url(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload())
            ->assertOk()
            ->assertJsonPath('data.storefront.slug', 'dorzak-merchant')
            ->assertJsonPath('data.storefront.public_url', 'http://127.0.0.1:3000/store/dorzak-merchant')
            ->assertJsonPath('data.storefront.banner_url', 'https://example.com/banner.jpg')
            ->assertJsonPath('data.storefront.allow_dine_in', true)
            ->assertJsonPath('data.storefront.dine_in_table_count', 12)
            ->assertJsonPath('data.storefront.delivery_fee', '5.00');
    }

    public function test_slug_is_lowercased_before_validation(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload(['store_slug' => 'Dorzak-Merchant']))
            ->assertOk()
            ->assertJsonPath('data.storefront.slug', 'dorzak-merchant');
    }

    public function test_slug_required_when_store_enabled(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload(['online_store_enabled' => true, 'store_slug' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('store_slug');
    }

    public function test_slug_rejects_invalid_characters(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload(['store_slug' => 'bad slug!']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('store_slug');
    }

    public function test_slug_rejects_reserved_words(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload(['store_slug' => 'admin']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('store_slug');
    }

    public function test_slug_must_be_globally_unique(): void
    {
        // Another store already owns the slug.
        $other = Store::factory()->create();
        $other->storefrontSetting->update(['slug' => 'taken-slug']);

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload(['store_slug' => 'taken-slug']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('store_slug');
    }

    public function test_store_may_keep_its_own_slug_on_resave(): void
    {
        $this->store->storefrontSetting->update(['slug' => 'dorzak-merchant']);

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload(['store_slug' => 'dorzak-merchant']))
            ->assertOk();
    }

    public function test_accent_color_must_be_hex(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload(['accent_color' => 'blue']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('accent_color');
    }

    public function test_storefront_layout_and_header_visibility_settings(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload([
                'product_card_layout' => 'horizontal',
                'show_store_header' => false,
                'show_store_gradient' => false,
            ]))
            ->assertOk()
            ->assertJsonPath('data.storefront.product_card_layout', 'horizontal')
            ->assertJsonPath('data.storefront.show_store_header', false)
            ->assertJsonPath('data.storefront.show_store_gradient', false);
    }

    public function test_product_card_layout_must_be_valid(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload(['product_card_layout' => 'diagonal']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('product_card_layout');
    }

    public function test_navbar_color_can_be_customized(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload([
                'navbar_color' => '#ff0000',
            ]))
            ->assertOk()
            ->assertJsonPath('data.storefront.navbar_color', '#ff0000');
    }

    public function test_navbar_color_must_be_hex(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/storefront', $this->payload(['navbar_color' => 'red']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('navbar_color');
    }
}
