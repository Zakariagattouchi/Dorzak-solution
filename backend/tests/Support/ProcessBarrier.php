<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

final class ProcessBarrier
{
    /** @return list<array<string, mixed>> */
    public static function run(string $operation, array $payloads): array
    {
        $qualification = [];
        foreach ([
            'DB_URL', 'P00_PG_IDENTITY', 'P00_PG_ATTESTATION_PATH',
            'P00_PG_ATTESTATION_SHA256', 'P00_PG_INSTANCE_NONCE_SHA256',
            'P00_PG_QUALIFICATION_NONCE_SHA256',
            'P00_PG_QUALIFICATION_PHASE', 'P00_PG_SCHEMA_READY',
            'P00_PG_QUALIFIED_CANDIDATE',
        ] as $name) {
            $value = getenv($name);
            if (! is_string($value) || $value === '') {
                throw new RuntimeException('Concurrency qualification environment is incomplete.');
            }
            $qualification[$name] = $value;
        }
        if ($qualification['P00_PG_QUALIFICATION_PHASE'] !== 'qualification'
            || $qualification['P00_PG_SCHEMA_READY'] !== '1'
            || $qualification['P00_PG_QUALIFIED_CANDIDATE'] !== '1'
            || getenv('P00_PG_TEST_SUBSTITUTE_URL_PATH') !== false) {
            throw new RuntimeException('Concurrency qualification environment is unsafe.');
        }
        $actors = [];
        try {
            foreach ($payloads as $payload) {
                try {
                    $input = new InputStream;
                    $process = new Process([
                        PHP_BINARY,
                        __DIR__.'/concurrency-worker.php',
                        $operation,
                        base64_encode(json_encode($payload, JSON_THROW_ON_ERROR)),
                    ], dirname(__DIR__, 2), array_merge($_SERVER, $_ENV, $qualification), null, 15);
                    $process->setInput($input);
                    $actors[] = [$process, $input];
                    $process->start();
                } catch (\Throwable) {
                    throw new RuntimeException('Concurrency actor failed during start phase.');
                }
            }

            $outputs = array_fill(0, count($actors), '');
            $ready = array_fill_keys(array_keys($actors), true);
            while ($ready !== []) {
                foreach (array_keys($ready) as $index) {
                    [$process] = $actors[$index];
                    self::pump($process, $outputs[$index]);
                    if (str_contains($outputs[$index], "READY\n")) {
                        unset($ready[$index]);

                        continue;
                    }
                    if (! $process->isRunning()) {
                        throw new RuntimeException('Concurrency actor exited during READY phase.');
                    }
                    try {
                        $process->checkTimeout();
                    } catch (ProcessTimedOutException) {
                        throw new RuntimeException('Concurrency READY phase timed out.');
                    }
                }
            }
            foreach ($actors as [$process, $input]) {
                $input->write("GO\n");
                $input->close();
                $process->setTimeout((microtime(true) - $process->getStartTime()) + 15);
            }
            $acknowledgements = array_fill_keys(array_keys($actors), true);
            while ($acknowledgements !== []) {
                foreach (array_keys($acknowledgements) as $index) {
                    [$process] = $actors[$index];
                    self::pump($process, $outputs[$index]);
                    if (str_contains($outputs[$index], "GO_RECEIVED\n")) {
                        $process->setTimeout((microtime(true) - $process->getStartTime()) + 15);
                        unset($acknowledgements[$index]);

                        continue;
                    }
                    if (! $process->isRunning()) {
                        throw new RuntimeException('Concurrency actor exited during GO acknowledgement phase.');
                    }
                    try {
                        $process->checkTimeout();
                    } catch (ProcessTimedOutException) {
                        throw new RuntimeException('Concurrency GO acknowledgement phase timed out.');
                    }
                }
            }

            $results = [];
            $pending = array_fill_keys(array_keys($actors), true);
            $observationStartedAt = hrtime(true);
            $snapshot = null;
            $snapshotAttempted = false;
            while ($pending !== []) {
                foreach (array_keys($pending) as $index) {
                    [$process] = $actors[$index];
                    self::pump($process, $outputs[$index]);
                    if (! $process->isRunning()) {
                        if (! $process->isSuccessful()) {
                            throw new RuntimeException(self::resultFailure('failed', $snapshot));
                        }
                        $lines = array_values(array_filter(explode("\n", trim($outputs[$index]))));
                        $results[$index] = json_decode((string) end($lines), true, flags: JSON_THROW_ON_ERROR);
                        unset($pending[$index]);

                        continue;
                    }
                    try {
                        $process->checkTimeout();
                    } catch (ProcessTimedOutException) {
                        throw new RuntimeException(self::resultFailure('timed out', $snapshot));
                    }
                }
                if ($pending !== [] && ! $snapshotAttempted && hrtime(true) - $observationStartedAt >= 2_000_000_000) {
                    $snapshotAttempted = true;
                    try {
                        $snapshot = self::activitySnapshot();
                    } catch (\Throwable) {
                        $snapshot = null;
                    }
                }
            }
            ksort($results);

            return array_values($results);
        } finally {
            self::stopActors($actors);
        }
    }

    private static function stopActors(array $actors): void
    {
        foreach ($actors as [$process, $input]) {
            $input->close();
            try {
                $process->stop(0);
            } catch (\Throwable) {
                // Preserve the active failure without exposing cleanup internals.
            }
        }
    }

    private static function pump(Process $process, string &$output): void
    {
        $output .= $process->getIncrementalOutput();
        $process->getIncrementalErrorOutput();
    }

    /** @return array{parent_transaction_level: int, sessions: list<array<string, bool|int|string|null>>} */
    private static function activitySnapshot(): array
    {
        $connection = DB::connection();
        $rows = $connection->select(<<<'SQL'
            SELECT
                state,
                wait_event_type,
                wait_event,
                cardinality(pg_blocking_pids(pid)) > 0 AS blocked,
                cardinality(pg_blocking_pids(pid)) AS blocker_count,
                CASE
                    WHEN position('for update' IN lower(query)) > 0
                        AND position('"stores"' IN lower(query)) > 0 THEN 'store-lock'
                    WHEN position('for update' IN lower(query)) > 0
                        AND position('"products"' IN lower(query)) > 0 THEN 'product-lock'
                    WHEN lower(query) LIKE 'insert into "orders"%'
                        OR lower(query) LIKE 'update "orders"%'
                        OR lower(query) LIKE 'insert into "order_items"%'
                        OR lower(query) LIKE 'update "order_items"%' THEN 'order-write'
                    WHEN lower(query) LIKE 'update "products"%'
                        OR lower(query) LIKE 'insert into "stock_movements"%'
                        OR lower(query) LIKE 'update "stock_movements"%' THEN 'product-write'
                    WHEN btrim(lower(query)) IN ('commit', 'rollback') THEN 'transaction-end'
                    ELSE 'other'
                END AS classifier
            FROM pg_stat_activity
            WHERE datname = current_database()
                AND usename = current_user
                AND pid <> pg_backend_pid()
            ORDER BY classifier, state, wait_event_type NULLS FIRST, wait_event NULLS FIRST
            SQL);

        return [
            'parent_transaction_level' => $connection->transactionLevel(),
            'sessions' => array_map(
                static fn (object $row): array => [
                    'state' => (string) $row->state,
                    'wait_event_type' => $row->wait_event_type === null ? null : (string) $row->wait_event_type,
                    'wait_event' => $row->wait_event === null ? null : (string) $row->wait_event,
                    'blocked' => filter_var($row->blocked, FILTER_VALIDATE_BOOL),
                    'blocker_count' => (int) $row->blocker_count,
                    'classifier' => (string) $row->classifier,
                ],
                $rows,
            ),
        ];
    }

    private static function resultFailure(string $reason, ?array $snapshot): string
    {
        $message = "Concurrency result phase {$reason}.";

        return $snapshot === null ? $message : $message.' '.json_encode($snapshot, JSON_THROW_ON_ERROR);
    }
}
