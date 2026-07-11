<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Exceptions\DomainConflictException;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Prepaid gift cards (premium — PlanFeature::GIFT_CARDS). Issuing is gated;
 * redeeming a card credits the recipient's store-credit wallet exactly once.
 */
class GiftCardService
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly WalletService $wallet,
    ) {}

    public function issue(Store $store, float $amount): GiftCard
    {
        $this->plans->ensure($store, PlanFeature::GIFT_CARDS);

        return GiftCard::create([
            'store_id' => $store->id,
            'code' => $this->uniqueCode($store),
            'amount' => $amount,
            'status' => 'active',
        ]);
    }

    public function redeem(Store $store, string $code, Customer $customer): GiftCard
    {
        return DB::transaction(function () use ($store, $code, $customer) {
            $card = GiftCard::where('store_id', $store->id)
                ->whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])
                ->lockForUpdate()
                ->first();

            if ($card === null) {
                throw new DomainConflictException('GIFT_CARD_INVALID', 'That gift card code is not valid.');
            }

            if ($card->status !== 'active') {
                throw new DomainConflictException('GIFT_CARD_REDEEMED', 'That gift card has already been redeemed.');
            }

            $this->wallet->credit($customer, (float) $card->amount, "Gift card {$card->code}");

            $card->update([
                'status' => 'redeemed',
                'redeemed_by_customer_id' => $customer->id,
                'redeemed_at' => now(),
            ]);

            return $card->fresh();
        });
    }

    private function uniqueCode(Store $store): string
    {
        do {
            $code = 'GC-'.strtoupper(Str::random(10));
        } while (GiftCard::where('store_id', $store->id)->where('code', $code)->exists());

        return $code;
    }
}
