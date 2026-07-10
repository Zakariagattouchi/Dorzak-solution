<?php

namespace Tests\Feature\PublicStorefront;

use App\Models\DeliveryProvider;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryQuoteEndpointTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['store' => $this->store] = $this->createStoreWithOwner();
        $this->store->update(['latitude' => 25.2854, 'longitude' => 51.531]);
        $this->store->storefrontSetting->update([
            'online_store_enabled' => true, 'slug' => 'quote-shop', 'allow_delivery' => true,
        ]);
        $this->assignPlan($this->store, 'PRO');
    }

    public function test_quote_returns_fee_provider_and_distance(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);

        $response = $this->getJson('/api/public/stores/quote-shop/delivery-quote?lat=25.325&lng=51.531&subtotal=30')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.available', true)
            ->assertJsonPath('data.mode', 'quoted')
            ->assertJsonPath('data.provider_name', 'Courier');

        $this->assertEqualsWithDelta(5 + 2 * $response->json('data.distance_km'), $response->json('data.fee'), 0.01);
    }

    public function test_quote_validates_coordinates(): void
    {
        $this->getJson('/api/public/stores/quote-shop/delivery-quote?lat=999&lng=51.5&subtotal=10')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lat']);

        $this->getJson('/api/public/stores/quote-shop/delivery-quote?subtotal=10')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lat', 'lng']);
    }

    public function test_unknown_slug_404s(): void
    {
        $this->getJson('/api/public/stores/nope/delivery-quote?lat=25.3&lng=51.5&subtotal=10')->assertNotFound();
    }

    public function test_plan_gated_provider_is_invisible_to_a_free_store(): void
    {
        $this->assignPlan($this->store, 'FREE');
        DeliveryProvider::create(['name' => 'Dorzak Delivery', 'base_fee' => 1, 'per_km_fee' => 1, 'min_fee' => 0, 'max_radius_km' => 50, 'is_plan_gated' => true]);

        $this->getJson('/api/public/stores/quote-shop/delivery-quote?lat=25.325&lng=51.531&subtotal=30')
            ->assertOk()
            ->assertJsonPath('data.available', false)
            ->assertJsonPath('data.mode', 'unavailable');
    }

    /**
     * The public store card is cached; setting the pickup pin must invalidate it
     * immediately, or a merchant would set their location and delivery would stay
     * dark for the whole cache TTL.
     */
    public function test_setting_the_store_pin_immediately_reopens_delivery_on_the_public_card(): void
    {
        DeliveryProvider::create(['name' => 'Courier', 'base_fee' => 5, 'per_km_fee' => 2, 'min_fee' => 0, 'max_radius_km' => 20]);

        $this->store->update(['latitude' => null, 'longitude' => null]);
        $this->getJson('/api/public/stores/quote-shop')
            ->assertOk()
            ->assertJsonPath('data.delivery_mode', 'none')
            ->assertJsonPath('data.allow_delivery', false);

        $this->store->update(['latitude' => 25.2854, 'longitude' => 51.531]);
        $this->getJson('/api/public/stores/quote-shop')
            ->assertOk()
            ->assertJsonPath('data.delivery_mode', 'quoted')
            ->assertJsonPath('data.allow_delivery', true);
    }
}
