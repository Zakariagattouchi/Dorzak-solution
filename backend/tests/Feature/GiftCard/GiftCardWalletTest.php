<?php

namespace Tests\Feature\GiftCard;

use App\Exceptions\DomainConflictException;
use App\Models\Customer;
use App\Models\Store;
use App\Services\GiftCardService;
use App\Services\WalletService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Premium feature: gift cards credit a customer's store-credit wallet. */
class GiftCardWalletTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create();
        app(StoreContext::class)->setStore($this->store);
    }

    public function test_crediting_and_reading_a_wallet_balance(): void
    {
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);

        app(WalletService::class)->credit($customer, 50, 'promo');

        $this->assertSame('50.00', number_format(app(WalletService::class)->balance($customer), 2));
    }

    public function test_redeeming_a_gift_card_credits_the_wallet_once(): void
    {
        // Create the card directly to keep this test off the plan gate (issue()
        // gating is covered by the API test).
        $card = \App\Models\GiftCard::create([
            'store_id' => $this->store->id, 'code' => 'GC-TEST12345', 'amount' => 75, 'status' => 'active',
        ]);
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);

        app(GiftCardService::class)->redeem($this->store, $card->code, $customer);

        $this->assertSame('75.00', number_format(app(WalletService::class)->balance($customer), 2));

        // A second redemption of the same card is refused.
        $this->expectException(DomainConflictException::class);
        app(GiftCardService::class)->redeem($this->store, $card->code, $customer);
    }

    public function test_redeeming_store_credit_debits_the_balance(): void
    {
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);
        app(WalletService::class)->credit($customer, 40, 'seed');

        app(WalletService::class)->redeem($customer, 25, 'checkout');

        $this->assertSame('15.00', number_format(app(WalletService::class)->balance($customer), 2));
    }

    public function test_overspending_store_credit_is_refused(): void
    {
        $customer = Customer::factory()->create(['store_id' => $this->store->id]);
        app(WalletService::class)->credit($customer, 10, 'seed');

        $this->expectException(DomainConflictException::class);
        app(WalletService::class)->redeem($customer, 25, 'checkout');
    }
}
