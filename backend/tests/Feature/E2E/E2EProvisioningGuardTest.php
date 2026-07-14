<?php

namespace Tests\Feature\E2E;

use App\Console\Commands\ServeE2e;
use App\Support\E2eDatabaseLease;
use App\Support\E2eSupervisor;
use App\Support\PostgresConnectionProfile;
use Closure;
use Illuminate\Database\Connection;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class E2EProvisioningGuardTest extends TestCase
{
    public function test_create_only_capability_refuses_substitution_and_never_resets_existing_state(): void
    {
        $attestation = FakeE2eSupervisor::attestation();
        $expected = [
            'environment' => 'e2e',
            'identity' => $attestation['identity'],
            'nonce' => $attestation['instanceNonceSha256'],
            'lifecycle' => $attestation['lifecycleId'],
            'contract' => str_repeat('c', 64),
        ];

        foreach ([
            ['environment', 'testing'],
            ['driver', 'sqlite'],
            ['identity', 'wrong-service'],
            ['serverMajor', 15],
            ['instanceNonceSha256', str_repeat('0', 64)],
            ['database', 'wrong-control'],
            ['role', 'wrong-supervisor'],
        ] as [$field, $value]) {
            $supervisor = new FakeE2eSupervisor;
            $input = $expected;
            if ($field === 'environment') {
                $input['environment'] = $value;
            } else {
                $supervisor->facts[$field] = $value;
            }
            $this->assertRefused(fn () => $this->acquire($supervisor, $attestation, $input));
            self::assertSame(0, $supervisor->createCalls, "wrong {$field} mutated the service");
        }

        $collision = new FakeE2eSupervisor;
        $collision->collision = true;
        $this->assertRefused(fn () => $this->acquire($collision, $attestation, $expected));
        self::assertSame(0, $collision->createCalls);

        $substitution = new FakeE2eSupervisor;
        $substitution->substituteServiceAfterCreate = true;
        $this->assertRefused(fn () => $this->acquire($substitution, $attestation, $expected));
        self::assertSame(['control' => 'unchanged', 'priorServer' => 'running'], $substitution->protectedState);
        self::assertFalse($substitution->activated);

        $first = new FakeE2eSupervisor;
        $second = new FakeE2eSupervisor;
        $leaseA = $this->acquire($first, $attestation, $expected, '11');
        $leaseB = $this->acquire($second, $attestation, $expected, '22');
        self::assertNotSame($leaseA->database(), $leaseB->database());
        self::assertSame([], $first->noncandidateDatabasesWithAccess($first->createdRole, $leaseA->database()));
        self::assertSame([], $second->noncandidateDatabasesWithAccess($second->createdRole, $leaseB->database()));

        foreach ([['migrate', '44'], ['db:seed', '55']] as [$failingCommand, $fill]) {
            $failureSupervisor = new FakeE2eSupervisor;
            $failureLease = $this->acquire($failureSupervisor, $attestation, $expected, $fill);
            $this->assertRefused(fn () => ServeE2e::prepare(
                $failureLease,
                function (array $command, array $environment) use ($failingCommand): void {
                    self::assertContains($environment['P00_E2E_PHASE'], ['provisioning-migrate', 'provisioning-seed']);
                    if (in_array($failingCommand, $command, true)) {
                        throw new RuntimeException($failingCommand.' failed');
                    }
                },
            ));
            self::assertFalse($failureSupervisor->activated);
            self::assertSame(['control' => 'unchanged', 'priorServer' => 'running'], $failureSupervisor->protectedState);
            self::assertSame('unchanged', $failureSupervisor->noncandidateFingerprint());
        }

        $leaseC = $this->acquire(new FakeE2eSupervisor, $attestation, $expected, '33');
        $successful = [];
        ServeE2e::prepare($leaseC, function (array $command, array $environment) use (&$successful): void {
            $successful[] = [$environment['P00_E2E_PHASE'], $command];
        });
        $leaseC->assertActive();
        self::assertSame([
            ['provisioning-migrate', [PHP_BINARY, 'artisan', 'migrate', '--database=e2e', '--force', '--no-interaction']],
            ['provisioning-seed', [PHP_BINARY, 'artisan', 'db:seed', '--database=e2e', '--class=Database\\Seeders\\E2ESeeder', '--force', '--no-interaction']],
        ], $successful);
        self::assertStringNotContainsString(
            'migrate:fresh db:wipe drop unlink rename reset',
            implode(' ', array_merge(...array_column($successful, 1))),
        );

        $leaseC->supervisorForTest()->candidateSubstitution = true;
        $this->assertRefused(fn () => $leaseC->assertActive());

        $this->assertPostgresProfileClosesUrlsAndResolvedLaravelConfiguration();
        $this->assertAttestationUsesOneCanonicalVerifiedRead($attestation);
        $this->assertSupervisorCredentialIsRemovedFromChildren();
    }

    private function assertPostgresProfileClosesUrlsAndResolvedLaravelConfiguration(): void
    {
        foreach (['disable', 'allow', 'prefer', 'require', 'verify-ca', 'verify-full'] as $sslmode) {
            $profile = PostgresConnectionProfile::fromUrl(
                "postgresql://p00_role:fixture-password@fixture.test/p00_database_test?sslmode={$sslmode}",
            );
            self::assertSame($sslmode, $profile->sslmode);
            self::assertSame(5432, $profile->port);
        }

        $profile = PostgresConnectionProfile::fromUrl(
            'postgresql://p00_role:fixture-password@fixture.test:5440/p00_database_test?sslmode=require',
        );
        $configuration = $profile->laravelConfiguration();
        $connection = new Connection(
            new PDO('sqlite::memory:'),
            $configuration['database'],
            '',
            ['name' => 'e2e-profile', ...$configuration],
        );
        $profile->assertLaravelConfiguration($connection, 'e2e-profile');

        foreach (['read', 'write', 'sticky', 'unix_socket', 'options'] as $override) {
            $overridden = ['name' => 'e2e-profile', ...$configuration, $override => []];
            $unsafe = new Connection(new PDO('sqlite::memory:'), $configuration['database'], '', $overridden);
            $this->assertRefused(fn () => $profile->assertLaravelConfiguration($unsafe, 'e2e-profile'));
        }

        foreach ([
            '',
            'postgresql://p00_role@fixture.test/p00_database_test',
            'postgresql://p00_role:@fixture.test/p00_database_test',
            'postgresql://:fixture-password@fixture.test/p00_database_test',
            'postgresql://p00_role:fixture password@fixture.test/p00_database_test',
            'postgresql://p00_role:fixture%3Bpassword@fixture.test/p00_database_test',
            'postgresql://p00%20role:fixture-password@fixture.test/p00_database_test',
            'postgresql://p00_role:fixture-password@bad_host/p00_database_test',
            'postgresql://p00_role:fixture-password@fixture.test/p00%2Fdatabase_test',
            'postgresql://p00_role:fixture-password@fixture.test/p00_database',
            'postgresql://p00_role:fixture-password@fixture.test:0/p00_database_test',
            'postgresql://p00_role:fixture-password@fixture.test:65536/p00_database_test',
            'postgresql://p00_role:fixture-password@fixture.test/p00_database_test?sslmode=require&sslmode=prefer',
            'postgresql://p00_role:fixture-password@fixture.test/p00_database_test?application_name=dorzak',
            'postgresql://p00_role:fixture-password@fixture.test/p00_database_test?sslmode=unknown',
            'postgresql://p00_role:fixture-password@fixture.test/p00_database_test#fragment',
            'postgresql://p00_role:fixture-password@fixture.test/p00_database_test%ZZ',
        ] as $unsafeUrl) {
            $this->assertRefused(fn () => PostgresConnectionProfile::fromUrl($unsafeUrl));
        }
    }

    private function assertAttestationUsesOneCanonicalVerifiedRead(array $attestation): void
    {
        $path = tempnam(sys_get_temp_dir(), 'p00-e2e-attestation-');
        if (! is_string($path)) {
            self::fail('Unable to create the attestation test fixture.');
        }
        $link = $path.'.link';

        try {
            $bytes = json_encode($attestation, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            file_put_contents($path, $bytes);
            $canonical = realpath($path);
            if (! is_string($canonical)) {
                self::fail('Unable to resolve the attestation test fixture.');
            }

            self::assertSame(
                $attestation,
                E2eDatabaseLease::attestationFromFile($canonical, hash('sha256', $bytes)),
            );
            $this->assertRefused(
                fn () => E2eDatabaseLease::attestationFromFile($canonical, str_repeat('0', 64)),
            );

            self::assertTrue(symlink($canonical, $link));
            $this->assertRefused(
                fn () => E2eDatabaseLease::attestationFromFile($link, hash('sha256', $bytes)),
            );
        } finally {
            if (is_link($link)) {
                unlink($link);
            }
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function assertSupervisorCredentialIsRemovedFromChildren(): void
    {
        $key = 'P00_E2E_SUPERVISOR_DB_URL';
        $hadServer = array_key_exists($key, $_SERVER);
        $serverValue = $_SERVER[$key] ?? null;
        $hadEnv = array_key_exists($key, $_ENV);
        $envValue = $_ENV[$key] ?? null;
        $processValue = getenv($key);

        $_SERVER[$key] = 'fixture-supervisor-credential';
        $_ENV[$key] = 'fixture-supervisor-credential';
        putenv($key.'=fixture-supervisor-credential');

        try {
            $environment = ServeE2e::childEnvironment([
                'P00_E2E_DB_URL' => 'postgresql://candidate:fixture@fixture.test/candidate_test',
            ], [
                $key => 'attempted-override',
            ]);
            self::assertFalse($environment[$key]);
            self::assertArrayHasKey('P00_E2E_DB_URL', $environment);

            $process = new Process(
                [PHP_BINARY, '-r', 'exit(getenv("P00_E2E_SUPERVISOR_DB_URL") === false ? 0 : 1);'],
                base_path(),
                $environment,
            );
            $process->run();
            self::assertTrue($process->isSuccessful());
        } finally {
            if ($hadServer) {
                $_SERVER[$key] = $serverValue;
            } else {
                unset($_SERVER[$key]);
            }
            if ($hadEnv) {
                $_ENV[$key] = $envValue;
            } else {
                unset($_ENV[$key]);
            }
            putenv($processValue === false ? $key : $key.'='.$processValue);
        }
    }

    private function acquire(
        FakeE2eSupervisor $supervisor,
        array $attestation,
        array $expected,
        string $fill = 'aa',
    ): E2eDatabaseLease {
        $bytes = [
            hex2bin(str_repeat($fill, 16)),
            str_repeat('p', 32),
            str_repeat('n', 32),
        ];

        return E2eDatabaseLease::acquire(
            $expected['environment'],
            $supervisor,
            $attestation,
            $expected['identity'],
            $expected['nonce'],
            $expected['lifecycle'],
            $expected['contract'],
            static function (int $length) use (&$bytes): string {
                $value = array_shift($bytes);
                if (! is_string($value) || strlen($value) !== $length) {
                    throw new RuntimeException('test entropy contract mismatch');
                }

                return $value;
            },
        );
    }

    private function assertRefused(Closure $attempt): void
    {
        try {
            $attempt();
            self::fail('Unsafe E2E operation was accepted.');
        } catch (RuntimeException) {
            self::assertTrue(true);
        }
    }
}

final class FakeE2eSupervisor implements E2eSupervisor
{
    public array $facts;

    public bool $collision = false;

    public bool $substituteServiceAfterCreate = false;

    public bool $candidateSubstitution = false;

    public bool $activated = false;

    public ?string $activationNonceSha256 = null;

    public ?string $fixtureContractSha256 = null;

    public int $createCalls = 0;

    public string $createdRole = '';

    public array $protectedState = ['control' => 'unchanged', 'priorServer' => 'running'];

    public function __construct()
    {
        $attestation = self::attestation();
        $this->facts = [
            'driver' => 'pgsql',
            'identity' => $attestation['identity'],
            'serverMajor' => 16,
            'instanceNonceSha256' => $attestation['instanceNonceSha256'],
            'database' => $attestation['supervisorDatabase'],
            'role' => $attestation['supervisorRole'],
        ];
    }

    public static function attestation(): array
    {
        return [
            'schemaVersion' => 1,
            'identity' => 'registry.example.test/postgres@sha256:'.str_repeat('a', 64),
            'serverMajor' => 16,
            'immutable' => true,
            'instanceNonceSha256' => str_repeat('b', 64),
            'lifecycleId' => 'container:p00-e2e-fixture-1',
            'supervisorDatabase' => 'postgres',
            'supervisorRole' => 'p00_supervisor',
            'containsRealData' => false,
            'canIssueIsolatedCredentials' => true,
            'noncandidateAccessDenied' => true,
        ];
    }

    public function facts(): array
    {
        if ($this->substituteServiceAfterCreate && $this->createCalls > 0) {
            return [...$this->facts, 'instanceNonceSha256' => str_repeat('f', 64)];
        }

        return $this->facts;
    }

    public function candidateExists(string $database, string $role): bool
    {
        return $this->collision;
    }

    public function noncandidateFingerprint(): string
    {
        return 'unchanged';
    }

    public function createCandidate(string $database, string $role, string $password): void
    {
        $this->createCalls++;
        $this->createdRole = $role;
    }

    public function candidateUrl(string $database, string $role, string $password): string
    {
        return "postgresql://{$role}:fixture-password@fixture.test/{$database}";
    }

    public function noncandidateDatabasesWithAccess(string $role, string $candidate): array
    {
        return [];
    }

    public function candidateFacts(string $url): array
    {
        $database = basename((string) parse_url($url, PHP_URL_PATH));
        $role = (string) parse_url($url, PHP_URL_USER);

        return [
            'driver' => 'pgsql',
            'database' => $this->candidateSubstitution ? 'substituted' : $database,
            'role' => $role,
            'serverMajor' => 16,
            'instanceNonceSha256' => str_repeat('b', 64),
            'superuser' => false,
            'createdb' => false,
            'createrole' => false,
            'bypassrls' => false,
            'replication' => false,
            'activationNonceSha256' => $this->activated ? $this->activationNonceSha256 : null,
            'fixtureContractSha256' => $this->activated ? $this->fixtureContractSha256 : null,
        ];
    }

    public function activate(
        string $url,
        string $database,
        string $role,
        string $activationNonceSha256,
        string $fixtureContractSha256,
        string $serviceNonceSha256,
    ): void {
        $this->activated = true;
        $this->activationNonceSha256 = $activationNonceSha256;
        $this->fixtureContractSha256 = $fixtureContractSha256;
    }
}
