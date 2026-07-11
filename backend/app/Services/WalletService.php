<?php

namespace App\Services;

use App\Exceptions\DomainConflictException;
use App\Models\Customer;
use App\Models\WalletAccount;
use App\Models\WalletEntry;
use Illuminate\Support\Facades\DB;

/**
 * Store-credit wallets. An append-only ledger backs a cached balance; store
 * credit is spent at checkout and topped up by gift-card redemptions.
 */
class WalletService
{
    public function balance(Customer $customer): float
    {
        return (float) (WalletAccount::where('customer_id', $customer->id)->value('balance') ?? 0);
    }

    public function credit(Customer $customer, float $amount, string $reason): void
    {
        $this->post($customer, abs($amount), $reason);
    }

    /** @throws DomainConflictException when the balance can't cover the amount. */
    public function redeem(Customer $customer, float $amount, string $reason): void
    {
        DB::transaction(function () use ($customer, $amount, $reason) {
            $account = WalletAccount::where('customer_id', $customer->id)->lockForUpdate()->first();

            if ($account === null || (float) $account->balance < $amount) {
                throw new DomainConflictException('INSUFFICIENT_CREDIT', 'Not enough store credit.');
            }

            $account->decrement('balance', $amount);
            WalletEntry::create([
                'store_id' => $account->store_id, 'customer_id' => $customer->id,
                'amount' => -$amount, 'reason' => $reason, 'created_at' => now(),
            ]);
        });
    }

    private function post(Customer $customer, float $amount, string $reason): void
    {
        DB::transaction(function () use ($customer, $amount, $reason) {
            $account = WalletAccount::firstOrCreate(
                ['store_id' => $customer->store_id, 'customer_id' => $customer->id],
                ['balance' => 0],
            );
            $account->increment('balance', $amount);
            WalletEntry::create([
                'store_id' => $customer->store_id, 'customer_id' => $customer->id,
                'amount' => $amount, 'reason' => $reason, 'created_at' => now(),
            ]);
        });
    }
}
