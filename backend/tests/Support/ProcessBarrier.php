<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

final class ProcessBarrier
{
    private const TIMEOUT_NANOSECONDS = 15_000_000_000;

    /**
     * @return array{
     *     blocked_on: 'stores'|'wallet_accounts',
     *     outcomes: list<array<string, mixed>>
     * }
     */
    public static function run(string $operation, array $payloads): array
    {
        $blockedOn = match ($operation) {
            'create-order' => 'stores',
            'redeem-wallet' => 'wallet_accounts',
            default => throw new RuntimeException('Concurrency operation is unsupported.'),
        };
        if (count($payloads) !== 2) {
            throw new RuntimeException('Concurrency requires exactly two actors.');
        }

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

        $deadline = hrtime(true) + self::TIMEOUT_NANOSECONDS;
        $actors = [];
        try {
            foreach (['holder', 'contender'] as $index => $role) {
                try {
                    $input = new InputStream;
                    $process = new Process([
                        PHP_BINARY,
                        __DIR__.'/concurrency-worker.php',
                        $operation,
                        base64_encode(json_encode($payloads[$index], JSON_THROW_ON_ERROR)),
                        $role,
                    ], dirname(__DIR__, 2), array_merge($_SERVER, $_ENV, $qualification), null, 15);
                    $process->setInput($input);
                    $actors[] = [$process, $input, $role];
                    $process->start();
                } catch (\Throwable) {
                    throw new RuntimeException('Concurrency actor failed during start phase.');
                }
            }

            $outputs = array_fill(0, count($actors), '');
            $bootstrapSeen = array_fill(0, count($actors), false);
            $pids = self::awaitReady($actors, $outputs, $bootstrapSeen, $deadline);

            self::writeCommand($actors[0][1], 'GO');
            self::awaitToken(
                $actors[0][0],
                $outputs[0],
                $bootstrapSeen[0],
                'HELD',
                $deadline,
            );

            self::writeCommand($actors[1][1], 'GO');
            self::awaitToken(
                $actors[1][0],
                $outputs[1],
                $bootstrapSeen[1],
                'ATTEMPT',
                $deadline,
            );
            self::awaitBlockingProof(
                $actors,
                $outputs,
                $bootstrapSeen,
                $pids[0],
                $pids[1],
                $deadline,
            );

            self::writeCommand($actors[0][1], 'RELEASE');
            $outcomes = self::collectOutcomes($actors, $outputs, $bootstrapSeen, $deadline);

            return ['blocked_on' => $blockedOn, 'outcomes' => $outcomes];
        } finally {
            self::stopActors($actors);
        }
    }

    /** @return list<int> */
    private static function awaitReady(
        array $actors,
        array &$outputs,
        array &$bootstrapSeen,
        int $deadline,
    ): array {
        $pending = array_fill_keys(array_keys($actors), true);
        $pids = [];
        while ($pending !== []) {
            self::assertBeforeDeadline($deadline);
            foreach (array_keys($pending) as $index) {
                [$process] = $actors[$index];
                self::pump($process, $outputs[$index], $bootstrapSeen[$index]);
                if (preg_match('/(?:^|\n)READY ([1-9][0-9]*)\n/', $outputs[$index], $matches) === 1) {
                    $pid = filter_var($matches[1], FILTER_VALIDATE_INT);
                    if (! is_int($pid) || $pid < 1 || in_array($pid, $pids, true)) {
                        throw new RuntimeException('Concurrency actor READY protocol failed.');
                    }
                    $pids[$index] = $pid;
                    unset($pending[$index]);

                    continue;
                }
                self::assertActorRunning($process, $deadline);
            }
        }
        ksort($pids);

        return array_values($pids);
    }

    private static function awaitToken(
        Process $process,
        string &$output,
        bool &$bootstrapSeen,
        string $token,
        int $deadline,
    ): void {
        while (true) {
            self::assertBeforeDeadline($deadline);
            self::pump($process, $output, $bootstrapSeen);
            if (preg_match('/(?:^|\n)'.preg_quote($token, '/').'\n/', $output) === 1) {
                return;
            }
            self::assertActorRunning($process, $deadline);
        }
    }

    private static function awaitBlockingProof(
        array $actors,
        array &$outputs,
        array &$bootstrapSeen,
        int $holderPid,
        int $contenderPid,
        int $deadline,
    ): void {
        while (true) {
            self::assertBeforeDeadline($deadline);
            foreach ($actors as $index => [$process]) {
                self::pump($process, $outputs[$index], $bootstrapSeen[$index]);
                self::assertActorRunning($process, $deadline);
            }

            try {
                $row = DB::selectOne(<<<'SQL'
                    SELECT wait_event_type, pg_blocking_pids(pid) AS blocking_pids
                    FROM pg_stat_activity
                    WHERE pid = CAST(? AS integer)
                        AND datname = current_database()
                        AND usename = current_user
                    SQL, [$contenderPid]);
            } catch (\Throwable) {
                throw new RuntimeException('Concurrency blocking proof failed.');
            }
            if (is_object($row)
                && ($row->wait_event_type ?? null) === 'Lock'
                && self::blockingPids($row->blocking_pids ?? null) === [$holderPid]) {
                return;
            }
        }
    }

    /** @return list<int> */
    private static function blockingPids(mixed $value): array
    {
        if (! is_string($value) || preg_match('/^\{([1-9][0-9]*)\}$/', $value, $matches) !== 1) {
            return [];
        }
        $pid = filter_var($matches[1], FILTER_VALIDATE_INT);

        return is_int($pid) && $pid > 0 ? [$pid] : [];
    }

    /** @return list<array<string, mixed>> */
    private static function collectOutcomes(
        array $actors,
        array &$outputs,
        array &$bootstrapSeen,
        int $deadline,
    ): array {
        $pending = array_fill_keys(array_keys($actors), true);
        $outcomes = [];
        while ($pending !== []) {
            self::assertBeforeDeadline($deadline);
            foreach (array_keys($pending) as $index) {
                [$process, , $role] = $actors[$index];
                self::pump($process, $outputs[$index], $bootstrapSeen[$index]);
                if ($process->isRunning()) {
                    self::assertActorWithinDeadline($process, $deadline);

                    continue;
                }
                if (! $process->isSuccessful()) {
                    throw new RuntimeException('Concurrency actor failed during outcome phase.');
                }
                $outcomes[$index] = self::parseOutcome($outputs[$index], $role);
                unset($pending[$index]);
            }
        }
        ksort($outcomes);

        return array_values($outcomes);
    }

    /** @return array<string, mixed> */
    private static function parseOutcome(string $output, string $role): array
    {
        $phase = $role === 'holder' ? 'HELD' : 'ATTEMPT';
        $pattern = '/\AREADY [1-9][0-9]*\n'.preg_quote($phase, '/').'\n([^\r\n]+)\n\z/';
        if (preg_match($pattern, $output, $matches) !== 1) {
            throw new RuntimeException('Concurrency actor outcome protocol failed.');
        }
        try {
            $outcome = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            throw new RuntimeException('Concurrency actor outcome protocol failed.');
        }
        if (! is_array($outcome) || ! is_bool($outcome['ok'] ?? null)) {
            throw new RuntimeException('Concurrency actor outcome protocol failed.');
        }

        return $outcome;
    }

    private static function writeCommand(InputStream $input, string $command): void
    {
        try {
            $input->write($command."\n");
        } catch (\Throwable) {
            throw new RuntimeException('Concurrency actor command failed.');
        }
    }

    private static function assertActorRunning(Process $process, int $deadline): void
    {
        self::assertBeforeDeadline($deadline);
        if (! $process->isRunning()) {
            throw new RuntimeException('Concurrency actor exited before barrier completion.');
        }
        self::assertActorWithinDeadline($process, $deadline);
    }

    private static function assertActorWithinDeadline(Process $process, int $deadline): void
    {
        self::assertBeforeDeadline($deadline);
        try {
            $process->checkTimeout();
        } catch (ProcessTimedOutException) {
            throw new RuntimeException('Concurrency barrier timed out.');
        }
    }

    private static function assertBeforeDeadline(int $deadline): void
    {
        if (hrtime(true) >= $deadline) {
            throw new RuntimeException('Concurrency barrier timed out.');
        }
    }

    private static function stopActors(array $actors): void
    {
        foreach ($actors as [$process, $input]) {
            try {
                if ($process->isRunning()) {
                    $input->write("ABORT\n");
                }
            } catch (\Throwable) {
                // Preserve the active result or failure.
            }
        }
        foreach ($actors as [$process, $input]) {
            try {
                $input->close();
            } catch (\Throwable) {
                // Preserve the active result or failure.
            }
            try {
                if ($process->isRunning()) {
                    $process->stop(0);
                }
            } catch (\Throwable) {
                // Preserve the active result or failure.
            }
        }
    }

    private static function pump(Process $process, string &$output, bool &$bootstrapSeen): void
    {
        $output .= $process->getIncrementalOutput();
        $process->getIncrementalErrorOutput();
        if ($bootstrapSeen) {
            return;
        }

        $lineEnd = strpos($output, "\n");
        if ($lineEnd === false) {
            if (strlen($output) > 256) {
                throw new RuntimeException('Concurrency actor bootstrap protocol failed.');
            }

            return;
        }
        $prelude = substr($output, 0, $lineEnd);
        if (preg_match(
            '/\AP00_POSTGRES_GUARD PASS database=p00_e2e_[0-9a-f]{32}_test server_version_num=16[0-9]{4}\z/',
            $prelude,
        ) !== 1) {
            throw new RuntimeException('Concurrency actor bootstrap protocol failed.');
        }
        $output = substr($output, $lineEnd + 1);
        $bootstrapSeen = true;
    }
}
