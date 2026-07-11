<?php

namespace Tests\Feature\Marketing;

use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Referral;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use App\Services\LoyaltyService;
use App\Services\WalletService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Marketing depth: the management controls the console needs. */
class MarketingControlsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
        $this->assignPlan($this->store, 'ENTERPRISE');
        app(StoreContext::class)->setStore($this->store);
    }

    public function test_a_draft_campaign_can_be_sent_now(): void
    {
        Customer::factory()->create(['store_id' => $this->store->id, 'email' => 'a@example.com']);
        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'Sale', 'body' => 'Now',
            'audience' => ['type' => 'all'], 'status' => 'draft',
        ]);

        $this->actingAsMember($this->owner)
            ->postJson("/api/v1/campaigns/{$campaign->id}/send")
            ->assertOk()
            ->assertJsonPath('status', 'sent');

        $this->assertSame(1, $campaign->refresh()->sent_count);
    }

    public function test_a_sent_campaign_cannot_be_sent_again(): void
    {
        $campaign = Campaign::create([
            'store_id' => $this->store->id, 'subject' => 'Sale', 'body' => 'Now',
            'audience' => ['type' => 'all'], 'status' => 'sent', 'sent_at' => now(),
        ]);

        $this->actingAsMember($this->owner)
            ->postJson("/api/v1/campaigns/{$campaign->id}/send")
            ->assertStatus(422);
    }

    public function test_a_coupon_can_be_deactivated_and_reactivated(): void
    {
        $coupon = Coupon::create(['store_id' => $this->store->id, 'code' => 'X', 'type' => 'PERCENT', 'value' => 10, 'active' => true]);

        $this->actingAsMember($this->owner)
            ->patchJson("/api/v1/coupons/{$coupon->id}", ['active' => false])
            ->assertOk();

        $this->assertFalse($coupon->refresh()->active);
    }

    public function test_referral_settings_include_program_stats(): void
    {
        $c1 = Customer::factory()->create(['store_id' => $this->store->id]);
        $c2 = Customer::factory()->create(['store_id' => $this->store->id]);
        Referral::create(['store_id' => $this->store->id, 'referrer_customer_id' => $c1->id, 'referred_customer_id' => $c2->id, 'code' => 'X', 'status' => 'rewarded']);
        app(WalletService::class)->credit($c1, 15, 'Referral reward');

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/settings/referrals')
            ->assertOk()
            ->assertJsonPath('stats.rewarded', 1)
            ->assertJsonPath('stats.credit_issued', 15);
    }

    public function test_loyalty_settings_include_program_stats(): void
    {
        app(LoyaltyService::class)->configure($this->store, ['enabled' => true, 'earn_points_per_currency' => 1, 'redeem_points' => 100, 'redeem_value' => 5]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);
        \App\Models\LoyaltyAccount::create(['store_id' => $this->store->id, 'customer_id' => $customer->id, 'points' => 250]);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/settings/loyalty')
            ->assertOk()
            ->assertJsonPath('stats.members', 1)
            ->assertJsonPath('stats.points_outstanding', 250);
    }

    public function test_coupon_list_includes_per_coupon_performance(): void
    {
        $this->store->update(['charge_sales_tax' => false]); // keep the math tax-free
        $coupon = Coupon::create(['store_id' => $this->store->id, 'code' => 'PERF', 'type' => 'PERCENT', 'value' => 10, 'active' => true]);
        $product = Product::factory()->for($this->store)->create(['price' => 100, 'track_stock' => false]);
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'payment_method' => 'CARD', 'coupon_code' => 'PERF',
            ])->assertCreated();

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/coupons')
            ->assertOk()
            ->assertJsonPath('coupons.0.orders', 1)
            ->assertJsonPath('coupons.0.revenue', 90)
            ->assertJsonPath('coupons.0.discount_given', 10);
    }

    public function test_reviews_list_includes_the_product_name(): void
    {
        $product = Product::factory()->for($this->store)->create(['name' => 'Signature Hoodie']);
        Review::create(['store_id' => $this->store->id, 'product_id' => $product->id, 'rating' => 5, 'approved' => false]);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/reviews')
            ->assertOk()
            ->assertJsonPath('reviews.0.product_name', 'Signature Hoodie');
    }
}
