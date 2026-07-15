<?php

namespace Tests\Postgres;

use App\Enums\StockMovementType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\WalletAccount;
use App\Models\WalletEntry;
use App\Services\WalletService;
use App\Support\StoreContext;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\Support\ProcessBarrier;
use Tests\TestCase;

final class OrderAndStockConcurrencyTest extends TestCase
{
    use DatabaseTruncation;

    protected array $exceptTables = ['p00_qualification_activation'];

    public function test_two_completed_orders_receive_distinct_numbers_and_atomic_stock_deductions(): void
    {
        $store = $this->store();
        $product = $this->product($store, 10);
        $payload = ['store_id' => $store->id, 'product_id' => $product->id, 'quantity' => 1];

        $run = ProcessBarrier::run('create-order', [$payload, $payload]);
        self::assertSame('stores', $run['blocked_on']);
        $results = $run['outcomes'];

        self::assertCount(2, $results);
        self::assertCount(2, array_filter($results, fn (array $result) => $result['ok'] === true));
        $numbers = array_column($results, 'order_number');
        sort($numbers, SORT_STRING);
        self::assertSame(['ORD-1000', 'ORD-1001'], $numbers);
        self::assertSame(2, Order::query()->count());
        self::assertSame(8, Product::query()->findOrFail($product->id)->stock);
        $movements = StockMovement::query()
            ->where('product_id', $product->id)
            ->where('type', StockMovementType::SALE->value)
            ->get();
        self::assertCount(2, $movements);
        self::assertSame(-2, $movements->sum('quantity_change'));
    }

    public function test_only_one_order_can_consume_the_last_stock_unit(): void
    {
        $store = $this->store();
        $product = $this->product($store, 1);
        $payload = ['store_id' => $store->id, 'product_id' => $product->id, 'quantity' => 1];

        $run = ProcessBarrier::run('create-order', [$payload, $payload]);
        self::assertSame('stores', $run['blocked_on']);
        $results = $run['outcomes'];

        self::assertCount(1, array_filter($results, fn (array $result) => $result['ok'] === true));
        $failures = array_values(array_filter($results, fn (array $result) => $result['ok'] === false));
        self::assertCount(1, $failures);
        self::assertSame('INSUFFICIENT_STOCK', $failures[0]['error_code']);
        self::assertSame(1, Order::query()->count());
        self::assertSame(0, Product::query()->findOrFail($product->id)->stock);
        $movements = StockMovement::query()
            ->where('product_id', $product->id)
            ->where('type', StockMovementType::SALE->value)
            ->get();
        self::assertCount(1, $movements);
        self::assertSame(-1, $movements->sum('quantity_change'));
    }

    public function test_only_one_concurrent_wallet_redemption_can_spend_available_credit(): void
    {
        $store = $this->store();
        $customer = Customer::factory()->for($store)->create();
        app(WalletService::class)->credit($customer, 10, 'P00 seed');
        $payload = ['store_id' => $store->id, 'customer_id' => $customer->id, 'amount' => 8];

        $run = ProcessBarrier::run('redeem-wallet', [$payload, $payload]);
        self::assertSame('wallet_accounts', $run['blocked_on']);
        $results = $run['outcomes'];

        self::assertCount(1, array_filter($results, fn (array $result) => $result['ok'] === true));
        $failures = array_values(array_filter($results, fn (array $result) => $result['ok'] === false));
        self::assertCount(1, $failures);
        self::assertSame('INSUFFICIENT_CREDIT', $failures[0]['error_code']);
        $account = WalletAccount::query()->where('customer_id', $customer->id)->sole();
        self::assertSame('2.00', (string) $account->balance);
        $debits = WalletEntry::query()
            ->where('customer_id', $customer->id)
            ->where('amount', '<', 0)
            ->get();
        self::assertCount(1, $debits);
        self::assertSame('-8.00', (string) $debits->sole()->amount);
    }

    private function store(): Store
    {
        $store = Store::factory()->create([
            'currency' => 'QAR',
            'charge_sales_tax' => false,
            'tax_rate' => 0,
        ]);
        app(StoreContext::class)->setStore($store);

        return $store;
    }

    private function product(Store $store, int $stock): Product
    {
        return Product::factory()->for($store)->create([
            'price' => 10,
            'cost' => 4,
            'taxable' => false,
            'track_stock' => true,
            'stock' => $stock,
            'min_stock' => 0,
            'is_active' => true,
        ]);
    }
}
