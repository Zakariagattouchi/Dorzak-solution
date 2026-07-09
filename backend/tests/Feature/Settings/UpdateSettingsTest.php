<?php

namespace Tests\Feature\Settings;

use App\Models\SettingsAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner] = $this->createStoreWithOwner();
    }

    public function test_update_general(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/general', [
                'business_name' => 'Dorzak Merchant',
                'tagline' => 'Commerce made simple',
                'phone' => '+1 555-0100',
                'whatsapp' => '+1 555-0100',
                'language' => 'ar',
            ])
            ->assertOk()
            ->assertJsonPath('data.general.business_name', 'Dorzak Merchant')
            ->assertJsonPath('data.general.language', 'ar');

        $this->assertDatabaseHas('stores', ['name' => 'Dorzak Merchant', 'language' => 'ar']);
    }

    public function test_update_general_requires_business_name_and_valid_language(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/general', ['business_name' => '', 'language' => 'fr'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['business_name', 'language']);
    }

    public function test_update_business_validates_country_whitelist(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/business', ['country' => 'Atlantis'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('country');

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/business', ['owner_name' => 'Barsha', 'country' => 'Qatar'])
            ->assertOk()
            ->assertJsonPath('data.business.country', 'Qatar');
    }

    public function test_update_business_persists_store_location(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/business', [
                'country' => 'Qatar',
                'latitude' => 25.2854123,
                'longitude' => 51.5310456,
            ])
            ->assertOk()
            ->assertJsonPath('data.business.latitude', 25.2854123)
            ->assertJsonPath('data.business.longitude', 51.5310456);

        $store = $this->owner->currentMembership()->store->fresh();
        $this->assertTrue($store->hasLocation());
    }

    public function test_update_business_rejects_half_a_coordinate_and_bad_ranges(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/business', ['country' => 'Qatar', 'latitude' => 25.28])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['longitude']);

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/business', ['country' => 'Qatar', 'latitude' => 95, 'longitude' => 51.5])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_currency_qar_forces_symbol_before(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/currency', [
                'currency' => 'QAR',
                'symbol_placement' => 'AFTER', // should be overridden to BEFORE
            ])
            ->assertOk()
            ->assertJsonPath('data.currency.currency', 'QAR')
            ->assertJsonPath('data.currency.symbol_placement', 'BEFORE')
            ->assertJsonPath('data.currency.currency_symbol', 'QAR');
    }

    public function test_currency_rejects_unknown_code(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/currency', ['currency' => 'XYZ', 'symbol_placement' => 'BEFORE'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('currency');
    }

    public function test_tax_rate_bounds_0_100(): void
    {
        $base = ['charge_sales_tax' => true, 'tax_included_in_price' => false];

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/taxes', $base + ['tax_rate' => 150])
            ->assertStatus(422)->assertJsonValidationErrors('tax_rate');

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/taxes', $base + ['tax_rate' => 8.5])
            ->assertOk()
            ->assertJsonPath('data.taxes.tax_rate', '8.50');
    }

    public function test_update_receipts(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/receipts', [
                'header' => 'Thanks for shopping!',
                'footer' => 'Returns within 30 days',
                'show_logo' => false,
                'show_address' => true,
                'show_tax' => true,
                'auto_print' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.receipts.header', 'Thanks for shopping!')
            ->assertJsonPath('data.receipts.show_logo', false)
            ->assertJsonPath('data.receipts.auto_print', true);
    }

    public function test_update_integrations_validates_id_formats(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/integrations', [
                'facebook_pixel_id' => 'abc',
                'google_analytics_id' => 'nope',
                'facebook_connected' => false,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['facebook_pixel_id', 'google_analytics_id']);

        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/integrations', [
                'facebook_pixel_id' => '123456789012',
                'google_analytics_id' => 'G-ABCD1234',
                'facebook_connected' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.integrations.facebook_pixel_id', '123456789012');
    }

    public function test_settings_write_creates_audit_log_row(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/currency', ['currency' => 'EUR', 'symbol_placement' => 'AFTER'])
            ->assertOk();

        $this->assertDatabaseCount('settings_audit_logs', 1);

        $log = SettingsAuditLog::firstOrFail();
        $this->assertSame('currency', $log->group);
        $this->assertSame($this->owner->id, $log->user_id);
        $this->assertSame('EUR', $log->new_values['currency']);
        $this->assertSame('USD', $log->old_values['currency']);
    }

    public function test_no_op_write_does_not_create_audit_row(): void
    {
        // Save the current currency values unchanged.
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/currency', ['currency' => 'USD', 'symbol_placement' => 'BEFORE'])
            ->assertOk();

        $this->assertDatabaseCount('settings_audit_logs', 0);
    }
}
