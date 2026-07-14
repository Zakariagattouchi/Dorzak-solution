<?php

use App\Exceptions\DomainConflictException;
use App\Models\Customer;
use App\Models\Store;
use App\Services\OrderService;
use App\Services\WalletService;
use App\Support\StoreContext;
use Illuminate\Contracts\Console\Kernel;

if ((getenv('P00_PG_QUALIFICATION_PHASE') ?: '') !== 'qualification'
    || getenv('P00_PG_SCHEMA_READY') !== '1'
    || getenv('P00_PG_QUALIFIED_CANDIDATE') !== '1') {
    fwrite(STDERR, "P00_CONCURRENCY_WORKER_REFUSED\n");
    exit(2);
}
require __DIR__.'/postgres-bootstrap.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$operation = (string) ($argv[1] ?? '');
$payload = json_decode(base64_decode((string) ($argv[2] ?? ''), true), true, flags: JSON_THROW_ON_ERROR);
$store = Store::findOrFail($payload['store_id']);
app(StoreContext::class)->setStore($store);
fwrite(STDOUT, "READY\n");
fflush(STDOUT);
if (trim((string) fgets(STDIN)) !== 'GO') {
    fwrite(STDERR, "Barrier command was not GO.\n");
    exit(2);
}
fwrite(STDOUT, "GO_RECEIVED\n");
fflush(STDOUT);

try {
    $result = match ($operation) {
        'create-order' => (static function () use ($store, $payload): array {
            $order = app(OrderService::class)->create($store, [
                'items' => [['product_id' => $payload['product_id'], 'quantity' => $payload['quantity']]],
                'payment_method' => 'CASH',
                'status' => 'COMPLETE',
            ]);

            return ['ok' => true, 'order_id' => $order->id, 'order_number' => $order->order_number];
        })(),
        'redeem-wallet' => (static function () use ($payload): array {
            $customer = Customer::findOrFail($payload['customer_id']);
            app(WalletService::class)->redeem($customer, (float) $payload['amount'], 'P00 concurrent checkout');

            return ['ok' => true];
        })(),
        default => throw new InvalidArgumentException("Unknown operation {$operation}"),
    };
} catch (DomainConflictException $exception) {
    $result = ['ok' => false, 'error_code' => $exception->errorCode];
}

fwrite(STDOUT, json_encode($result, JSON_THROW_ON_ERROR)."\n");
