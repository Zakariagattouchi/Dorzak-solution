<?php

namespace Tests\Feature\Settings;

use App\Enums\StaffRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_grouped_settings_envelope(): void
    {
        ['user' => $user, 'store' => $store] = $this->createStoreWithOwner([
            'currency' => 'USD', 'tax_rate' => 8.5,
        ]);

        $this->actingAsMember($user)
            ->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'general' => ['business_name', 'tagline', 'phone', 'whatsapp', 'language'],
                'business' => ['owner_name', 'email', 'address', 'city', 'state', 'zip_code', 'country'],
                'currency' => ['currency', 'currency_symbol', 'symbol_placement'],
                'taxes' => ['charge_sales_tax', 'tax_rate', 'tax_id', 'tax_included_in_price'],
                'payments' => ['cash', 'card', 'transfer', 'whatsapp'],
                'receipts' => ['header', 'footer', 'show_logo', 'show_address', 'show_tax', 'auto_print'],
                'integrations' => ['facebook_pixel_id', 'google_analytics_id', 'facebook_connected', 'facebook_page_name'],
                'storefront' => ['online_store_enabled', 'slug', 'public_url', 'bio', 'banner_url', 'logo_url', 'accent_color', 'allow_delivery', 'allow_pickup', 'delivery_fee', 'free_delivery_threshold', 'min_order_amount', 'whatsapp_ordering_enabled', 'show_out_of_stock_online'],
            ]])
            ->assertJsonPath('data.general.business_name', $store->name)
            ->assertJsonPath('data.currency.currency', 'USD')
            ->assertJsonPath('data.currency.currency_symbol', '$');
    }

    public function test_envelope_reflects_default_settings_rows(): void
    {
        ['user' => $user] = $this->createStoreWithOwner();

        $this->actingAsMember($user)
            ->getJson('/api/v1/settings')
            ->assertOk()
            // Defaults from the 1:1 settings tables seeded at store creation.
            ->assertJsonPath('data.storefront.online_store_enabled', false)
            ->assertJsonPath('data.storefront.delivery_fee', '0.00')
            ->assertJsonPath('data.receipts.show_logo', true)
            ->assertJsonPath('data.integrations.facebook_connected', false)
            ->assertJsonPath('data.payments.cash', true);
    }

    public function test_viewer_can_read_settings(): void
    {
        $viewer = $this->createMember(StaffRole::VIEWER);

        $this->actingAsMember($viewer)
            ->getJson('/api/v1/settings')
            ->assertOk();
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/settings')->assertUnauthorized();
    }
}
