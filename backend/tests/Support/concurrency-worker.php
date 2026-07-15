<?php

declare(strict_types=1);

use App\Exceptions\DomainConflictException;
use App\Models\Customer;
use App\Models\Store;
use App\Services\OrderService;
use App\Services\WalletService;
use App\Support\StoreContext;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

if ((getenv('P00_PG_QUALIFICATION_PHASE') ?: '') !== 'qualification'
    || getenv('P00_PG_SCHEMA_READY') !== '1'
    || getenv('P00_PG_QUALIFIED_CANDIDATE') !== '1') {
    fwrite(STDERR, "P00_CONCURRENCY_WORKER_REFUSED\n");
    exit(2);
}

require __DIR__.'/postgres-bootstrap.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    $operation = (string) ($argv[1] ?? '');
    $encodedPayload = (string) ($argv[2] ?? '');
    $role = (string) ($argv[3] ?? '');
    if (! in_array($operation, ['create-order', 'redeem-wallet'], true)
        || ! in_array($role, ['holder', 'contender'], true)) {
        throw new RuntimeException;
    }
    $decodedPayload = base64_decode($encodedPayload, true);
    $payload = is_string($decodedPayload)
        ? json_decode($decodedPayload, true, flags: JSON_THROW_ON_ERROR)
        : null;
    if (! is_array($payload) || ! isset($payload['store_id'])) {
        throw new RuntimeException;
    }

    $store = Store::findOrFail($payload['store_id']);
    app(StoreContext::class)->setStore($store);
    $connection = DB::connection();
    $pdo = $connection->getPdo();
    $pidStatement = $pdo->query('SELECT pg_backend_pid()');
    $pid = $pidStatement === false ? 0 : (int) $pidStatement->fetchColumn();
    if ($pid < 1) {
        throw new RuntimeException;
    }

    $assertSealedPdo = static function () use ($connection, $pdo, $pid): void {
        if ($connection->getPdo() !== $pdo) {
            throw new RuntimeException;
        }
        $statement = $pdo->query('SELECT pg_backend_pid()');
        if ($statement === false || (int) $statement->fetchColumn() !== $pid) {
            throw new RuntimeException;
        }
    };
    $readCommand = static function (): string {
        $line = fgets(STDIN);
        if (! is_string($line)) {
            throw new RuntimeException;
        }

        return rtrim($line, "\r\n");
    };
    $writeToken = static function (string $token): void {
        fwrite(STDOUT, $token."\n");
        fflush(STDOUT);
    };
    $performOperation = static function () use ($operation, $store, $payload): array {
        return match ($operation) {
            'create-order' => (static function () use ($store, $payload): array {
                $order = app(OrderService::class)->create($store, [
                    'items' => [[
                        'product_id' => $payload['product_id'],
                        'quantity' => $payload['quantity'],
                    ]],
                    'payment_method' => 'CASH',
                    'status' => 'COMPLETE',
                ]);

                return [
                    'ok' => true,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ];
            })(),
            'redeem-wallet' => (static function () use ($payload): array {
                $customer = Customer::findOrFail($payload['customer_id']);
                app(WalletService::class)->redeem(
                    $customer,
                    (float) $payload['amount'],
                    'P00 concurrent checkout',
                );

                return ['ok' => true];
            })(),
        };
    };
    $performOutcome = static function () use ($performOperation): array {
        try {
            return $performOperation();
        } catch (DomainConflictException $exception) {
            return ['ok' => false, 'error_code' => $exception->errorCode];
        }
    };

    $writeToken("READY {$pid}");
    if ($readCommand() !== 'GO') {
        throw new RuntimeException;
    }

    if ($role === 'holder') {
        $targetTable = $operation === 'create-order' ? 'stores' : 'wallet_accounts';
        $held = false;
        DB::listen(static function (QueryExecuted $query) use (
            &$held,
            $targetTable,
            $assertSealedPdo,
            $readCommand,
            $writeToken,
        ): void {
            if ($held) {
                return;
            }
            $normalized = preg_replace('/\s+/', ' ', strtolower(trim($query->sql)));
            if (! is_string($normalized)
                || ! str_starts_with($normalized, 'select ')
                || ! str_contains($normalized, ' from "'.$targetTable.'" ')
                || ! str_ends_with($normalized, ' for update')) {
                return;
            }

            $assertSealedPdo();
            $held = true;
            $writeToken('HELD');
            if ($readCommand() !== 'RELEASE') {
                throw new RuntimeException;
            }
        });

        $connection->beginTransaction();
        $outcome = $performOutcome();
        if (! $held) {
            throw new RuntimeException;
        }
        $connection->commit();
    } else {
        $assertSealedPdo();
        $writeToken('ATTEMPT');
        $outcome = $performOutcome();
    }

    fwrite(STDOUT, json_encode($outcome, JSON_THROW_ON_ERROR)."\n");
    fflush(STDOUT);
} catch (Throwable) {
    try {
        if (isset($connection)) {
            while ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }
        }
    } catch (Throwable) {
        // Preserve the fixed worker failure.
    }
    fwrite(STDERR, "P00_CONCURRENCY_WORKER_FAILURE\n");
    exit(2);
}
