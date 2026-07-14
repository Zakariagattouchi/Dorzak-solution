<?php

namespace Tests\Feature\Marketing;

use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\LoyaltyAccount;
use App\Models\Product;
use App\Models\Referral;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Marketing depth: one endpoint powering the console's overview stat strip. */
class MarketingOverviewTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
        app(StoreContext::class)->setStore($this->store);
    }

    public function test_overview_aggregates_every_marketing_surface(): void
    {
        $sid = $this->store->id;
        Coupon::create(['store_id' => $sid, 'code' => 'A', 'type' => 'PERCENT', 'value' => 10, 'active' => true, 'used_count' => 3]);
        Coupon::create(['store_id' => $sid, 'code' => 'B', 'type' => 'FIXED', 'value' => 5, 'active' => false]);
        Campaign::create(['store_id' => $sid, 'subject' => 's', 'body' => 'b', 'audience' => ['type' => 'all'], 'status' => 'sent', 'sent_count' => 12, 'sent_at' => now()]);
        $c1 = Customer::factory()->create(['store_id' => $sid]);
        $c2 = Customer::factory()->create(['store_id' => $sid]);
        Referral::create(['store_id' => $sid, 'referrer_customer_id' => $c1->id, 'referred_customer_id' => $c2->id, 'code' => 'X', 'status' => 'rewarded']);
        GiftCard::create(['store_id' => $sid, 'code' => 'GC-1', 'amount' => 50, 'status' => 'active']);
        GiftCard::create(['store_id' => $sid, 'code' => 'GC-2', 'amount' => 25, 'status' => 'redeemed']);
        LoyaltyAccount::create(['store_id' => $sid, 'customer_id' => $c1->id, 'points' => 120]);
        $product = Product::factory()->for($this->store)->create();
        Review::create(['store_id' => $sid, 'product_id' => $product->id, 'rating' => 4, 'approved' => true]);
        Review::create(['store_id' => $sid, 'product_id' => $product->id, 'rating' => 2, 'approved' => false]);

        $this->actingAsMember($this->owner)
            ->getJson('/api/v1/marketing/overview')
            ->assertOk()
            ->assertJsonPath('coupons.active', 1)
            ->assertJsonPath('coupons.redemptions', 3)
            ->assertJsonPath('campaigns.sent', 1)
            ->assertJsonPath('campaigns.recipients', 12)
            ->assertJsonPath('referrals.rewarded', 1)
            ->assertJsonPath('gift_cards.outstanding_value', 50)
            ->assertJsonPath('gift_cards.redeemed', 1)
            ->assertJsonPath('loyalty.members', 1)
            ->assertJsonPath('loyalty.points_outstanding', 120)
            ->assertJsonPath('reviews.pending', 1)
            ->assertJsonPath('reviews.average', 4);
    }
}
