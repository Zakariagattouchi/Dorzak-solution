<?php

namespace Tests\Support;

use App\Console\Commands\ServeE2e;
use App\Support\E2eDatabaseLease;
use App\Support\PdoE2eSupervisor;
use App\Support\PostgresConnectionProfile;
use App\Support\PostgresQualificationGuard;
use Database\Seeders\E2ESeeder;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;

final class PostgresSubstitutionHarness
{
    private function __construct(
        private readonly PdoE2eSupervisor $supervisor,
        private readonly array $attestation,
    ) {}

    public static function fromEnvironment(): self
    {
        $path = (string) env('P00_E2E_SERVICE_ATTESTATION_PATH');
        $sha = (string) env('P00_E2E_SERVICE_ATTESTATION_SHA256');
        try {
            $attestation = E2eDatabaseLease::attestationFromFile($path, $sha);
        } catch (RuntimeException $error) {
            throw new RuntimeException('P00_LIVE_HARNESS_ATTESTATION_REFUSED', 0, $error);
        }

        return new self(
            new PdoE2eSupervisor(
                (string) env('P00_E2E_SUPERVISOR_DB_URL'),
                (string) env('P00_PG_IDENTITY'),
            ),
            $attestation,
        );
    }

    public function candidate(): E2eDatabaseLease
    {
        return E2eDatabaseLease::acquire(
            'e2e',
            $this->supervisor,
            $this->attestation,
            (string) env('P00_PG_IDENTITY'),
            (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
            (string) env('P00_E2E_SERVICE_LIFECYCLE_ID'),
            hash('sha256', E2ESeeder::CONTRACT_JSON),
        );
    }

    public function profile(E2eDatabaseLease $lease): PostgresConnectionProfile
    {
        return PostgresConnectionProfile::fromUrl(
            $lease->environment('provisioning-migrate')['P00_E2E_DB_URL'],
        );
    }

    public function pdo(E2eDatabaseLease $lease, string $phase = 'provisioning-migrate'): PDO
    {
        $pdo = $this->profile($lease)->pdo();
        $facts = $lease->environment($phase);
        E2eDatabaseLease::assertBootConnection(
            $pdo,
            $facts['P00_E2E_DATABASE'],
            $facts['P00_E2E_ROLE'],
            $facts['P00_PG_INSTANCE_NONCE_SHA256'],
            $facts['P00_E2E_ACTIVATION_NONCE_SHA256'],
            $facts['P00_E2E_FIXTURE_CONTRACT_SHA256'],
            $phase,
        );

        return $pdo;
    }

    public function terminateBackend(E2eDatabaseLease $lease, int $pid): void
    {
        $this->supervisor->terminateBackend($lease->database(), $lease->role(), $pid);
    }

    public function activateQualification(E2eDatabaseLease $lease, string $nonceSha256): void
    {
        $pdo = $this->pdo($lease);
        PostgresQualificationGuard::activateCandidate($pdo, $nonceSha256);
    }

    public function installCanary(E2eDatabaseLease $lease): string
    {
        $pdo = $this->pdo($lease);
        $pdo->exec('CREATE TABLE p00_guard_canary (singleton boolean PRIMARY KEY, value char(64) NOT NULL)');
        $pdo->exec("INSERT INTO p00_guard_canary VALUES (true, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')");

        return $this->canaryFingerprint($lease);
    }

    public function canaryFingerprint(E2eDatabaseLease $lease): string
    {
        $rows = $this->pdo($lease)->query(
            'SELECT singleton, value FROM p00_guard_canary ORDER BY singleton',
        )->fetchAll(PDO::FETCH_ASSOC);

        return hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR));
    }

    public function activationExists(E2eDatabaseLease $lease, bool $active = false): bool
    {
        return $this->pdo($lease, $active ? 'active' : 'provisioning-migrate')
            ->query("SELECT to_regclass('public.p00_e2e_activation') IS NOT NULL")
            ->fetchColumn() === true;
    }

    public function runChild(
        E2eDatabaseLease $expected,
        array $command,
        string $phase,
        ?E2eDatabaseLease $substitute = null,
        array $overrides = [],
    ): Process {
        $environment = $expected->environment($phase);
        if ($substitute !== null) {
            $environment['P00_E2E_DB_URL'] = $substitute->environment($phase)['P00_E2E_DB_URL'];
        }
        $process = new Process(
            $command,
            base_path(),
            ServeE2e::childEnvironment($environment, $overrides),
        );
        $process->setTimeout(300)->run();

        return $process;
    }
}
