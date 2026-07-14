<?php

namespace App\Support;

use Closure;
use PDO;
use RuntimeException;
use Throwable;

final class E2eDatabaseLease
{
    private const ATTESTATION_KEYS = [
        'schemaVersion',
        'identity',
        'serverMajor',
        'immutable',
        'instanceNonceSha256',
        'lifecycleId',
        'supervisorDatabase',
        'supervisorRole',
        'containsRealData',
        'canIssueIsolatedCredentials',
        'noncandidateAccessDenied',
    ];

    private function __construct(
        private readonly E2eSupervisor $supervisor,
        private readonly string $database,
        private readonly string $role,
        private readonly string $url,
        private readonly string $identity,
        private readonly string $serviceNonceSha256,
        private readonly string $lifecycleId,
        private readonly string $activationNonceSha256,
        private readonly string $fixtureContractSha256,
    ) {}

    public static function acquire(
        string $environment,
        E2eSupervisor $supervisor,
        array $attestation,
        string $approvedIdentity,
        string $approvedServiceNonceSha256,
        string $approvedLifecycleId,
        string $fixtureContractSha256,
        ?Closure $randomBytes = null,
    ): self {
        if ($environment !== 'e2e'
            || array_keys($attestation) !== self::ATTESTATION_KEYS
            || $attestation['schemaVersion'] !== 1
            || $attestation['identity'] !== $approvedIdentity
            || $attestation['serverMajor'] !== 16
            || $attestation['immutable'] !== true
            || $attestation['instanceNonceSha256'] !== $approvedServiceNonceSha256
            || $attestation['lifecycleId'] !== $approvedLifecycleId
            || $attestation['containsRealData'] !== false
            || $attestation['canIssueIsolatedCredentials'] !== true
            || $attestation['noncandidateAccessDenied'] !== true
            || ! preg_match('/^[0-9a-f]{64}$/', $fixtureContractSha256)) {
            throw new RuntimeException('E2E service authority is incomplete or unsafe.');
        }

        self::assertSupervisorFacts($supervisor->facts(), $attestation);
        $before = $supervisor->noncandidateFingerprint();
        $entropy = $randomBytes ?? random_bytes(...);
        $suffix = bin2hex($entropy(16));
        $database = 'p00_e2e_'.$suffix.'_test';
        $role = 'p00_e2e_r_'.$suffix;
        $password = rtrim(strtr(base64_encode($entropy(32)), '+/', '-_'), '=');
        $activationNonceSha256 = hash('sha256', $entropy(32));

        if ($supervisor->candidateExists($database, $role)) {
            throw new RuntimeException('E2E candidate collision refused; no name is reused.');
        }

        try {
            $supervisor->createCandidate($database, $role, $password);
            self::assertSupervisorFacts($supervisor->facts(), $attestation);
            if (! hash_equals($before, $supervisor->noncandidateFingerprint())) {
                throw new RuntimeException('Noncandidate database metadata changed.');
            }
            if ($supervisor->noncandidateDatabasesWithAccess($role, $database) !== []) {
                throw new RuntimeException('Candidate role can access a noncandidate database.');
            }

            $url = $supervisor->candidateUrl($database, $role, $password);
            $lease = new self(
                $supervisor,
                $database,
                $role,
                $url,
                $approvedIdentity,
                $approvedServiceNonceSha256,
                $approvedLifecycleId,
                $activationNonceSha256,
                $fixtureContractSha256,
            );
            $lease->assertCandidate(false);

            return $lease;
        } catch (Throwable $error) {
            throw new E2eProvisioningException($database, $role, $approvedLifecycleId, $error);
        }
    }

    public function database(): string
    {
        return $this->database;
    }

    public function role(): string
    {
        return $this->role;
    }

    public function lifecycleId(): string
    {
        return $this->lifecycleId;
    }

    public function environment(string $phase): array
    {
        return [
            'APP_ENV' => 'e2e',
            'DB_CONNECTION' => 'e2e',
            'P00_E2E_PHASE' => $phase,
            'P00_E2E_DB_URL' => $this->url,
            'P00_E2E_DATABASE' => $this->database,
            'P00_E2E_ROLE' => $this->role,
            'P00_PG_IDENTITY' => $this->identity,
            'P00_PG_INSTANCE_NONCE_SHA256' => $this->serviceNonceSha256,
            'P00_E2E_ACTIVATION_NONCE_SHA256' => $this->activationNonceSha256,
            'P00_E2E_FIXTURE_CONTRACT_SHA256' => $this->fixtureContractSha256,
            'P00_E2E_SERVICE_LIFECYCLE_ID' => $this->lifecycleId,
        ];
    }

    public function activate(): void
    {
        $this->assertCandidate(false);
        $this->supervisor->activate(
            $this->url,
            $this->database,
            $this->role,
            $this->activationNonceSha256,
            $this->fixtureContractSha256,
            $this->serviceNonceSha256,
        );
        $this->assertActive();
    }

    public function assertActive(): void
    {
        $this->assertCandidate(true);
    }

    public function supervisorForTest(): E2eSupervisor
    {
        if (! app()->environment('testing')) {
            throw new RuntimeException('Test seam is unavailable outside tests.');
        }

        return $this->supervisor;
    }

    public static function attestationFromFile(string $path, string $sha256): array
    {
        $pathFacts = @lstat($path);
        if (! preg_match('/^[0-9a-f]{64}$/', $sha256)
            || ! is_array($pathFacts)
            || is_link($path)
            || realpath($path) !== $path
            || ($pathFacts['mode'] & 0170000) !== 0100000) {
            throw new RuntimeException('E2E service attestation is absent or changed.');
        }

        $handle = @fopen($path, 'rb');
        if (! is_resource($handle)) {
            throw new RuntimeException('E2E service attestation is absent or changed.');
        }

        try {
            $openFacts = fstat($handle);
            $bytes = stream_get_contents($handle);
        } finally {
            fclose($handle);
        }
        if (! is_array($openFacts)
            || $openFacts['dev'] !== $pathFacts['dev']
            || $openFacts['ino'] !== $pathFacts['ino']
            || ($openFacts['mode'] & 0170000) !== 0100000
            || ! is_string($bytes)
            || ! hash_equals($sha256, hash('sha256', $bytes))) {
            throw new RuntimeException('E2E service attestation is absent or changed.');
        }

        try {
            $attestation = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $error) {
            throw new RuntimeException('E2E service attestation is absent or changed.', 0, $error);
        }
        if (! is_array($attestation)) {
            throw new RuntimeException('E2E service attestation is absent or changed.');
        }

        return $attestation;
    }

    public static function assertBootConnection(
        PDO $pdo,
        string $database,
        string $role,
        string $serviceNonceSha256,
        string $activationNonceSha256,
        string $fixtureContractSha256,
        string $phase,
    ): void {
        $row = $pdo->query(
            "SELECT current_database() AS database, current_user AS role,
            current_setting('server_version_num')::int / 10000 AS server_major,
            current_setting('dorzak.instance_nonce_sha256') AS service_nonce_sha256,
            rolsuper, rolcreatedb, rolcreaterole, rolbypassrls, rolreplication,
            to_regclass('public.p00_e2e_activation') AS activation_table
            FROM pg_roles WHERE rolname = current_user",
        )->fetch(PDO::FETCH_ASSOC);
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql'
            || ! in_array($phase, ['provisioning-migrate', 'provisioning-seed', 'provisioning-activate', 'active'], true)
            || ! is_array($row)
            || $row['database'] !== $database
            || $row['role'] !== $role
            || (int) $row['server_major'] !== 16
            || $row['service_nonce_sha256'] !== $serviceNonceSha256
            || $row['rolsuper'] !== false
            || $row['rolcreatedb'] !== false
            || $row['rolcreaterole'] !== false
            || $row['rolbypassrls'] !== false
            || $row['rolreplication'] !== false) {
            throw new RuntimeException('P00_E2E_LIVE_PDO_REFUSED');
        }
        if ($phase !== 'active') {
            if ($row['activation_table'] !== null) {
                throw new RuntimeException('Provisioning child requires an unactivated candidate.');
            }

            return;
        }

        $activation = $pdo->query(
            'SELECT activation_nonce_sha256, fixture_contract_sha256, service_nonce_sha256
             FROM p00_e2e_activation WHERE singleton = true',
        )->fetch(PDO::FETCH_ASSOC);
        if (! is_array($activation)
            || $activation['activation_nonce_sha256'] !== $activationNonceSha256
            || $activation['fixture_contract_sha256'] !== $fixtureContractSha256
            || $activation['service_nonce_sha256'] !== $serviceNonceSha256) {
            throw new RuntimeException('Active E2E connection identity mismatch.');
        }
    }

    private function assertCandidate(bool $active): void
    {
        $facts = $this->supervisor->candidateFacts($this->url);
        $expectedKeys = [
            'driver', 'database', 'role', 'serverMajor', 'instanceNonceSha256',
            'superuser', 'createdb', 'createrole', 'bypassrls', 'replication',
            'activationNonceSha256', 'fixtureContractSha256',
        ];
        if (array_keys($facts) !== $expectedKeys
            || $facts['driver'] !== 'pgsql'
            || $facts['database'] !== $this->database
            || $facts['role'] !== $this->role
            || $facts['serverMajor'] !== 16
            || $facts['instanceNonceSha256'] !== $this->serviceNonceSha256
            || $facts['superuser'] !== false
            || $facts['createdb'] !== false
            || $facts['createrole'] !== false
            || $facts['bypassrls'] !== false
            || $facts['replication'] !== false
            || $facts['activationNonceSha256'] !== ($active ? $this->activationNonceSha256 : null)
            || $facts['fixtureContractSha256'] !== ($active ? $this->fixtureContractSha256 : null)) {
            throw new RuntimeException('E2E candidate capability identity mismatch.');
        }
    }

    private static function assertSupervisorFacts(array $facts, array $attestation): void
    {
        if (array_keys($facts) !== ['driver', 'identity', 'serverMajor', 'instanceNonceSha256', 'database', 'role']
            || $facts['driver'] !== 'pgsql'
            || $facts['identity'] !== $attestation['identity']
            || $facts['serverMajor'] !== 16
            || $facts['instanceNonceSha256'] !== $attestation['instanceNonceSha256']
            || $facts['database'] !== $attestation['supervisorDatabase']
            || $facts['role'] !== $attestation['supervisorRole']) {
            throw new RuntimeException('Live E2E supervisor does not match its attestation.');
        }
    }
}
