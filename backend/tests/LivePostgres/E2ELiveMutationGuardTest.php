<?php

namespace Tests\LivePostgres;

use App\Support\E2eDatabaseLease;
use App\Support\PdoE2eSupervisor;
use App\Support\PostgresConnectionProfile;
use Illuminate\Database\Connectors\ConnectionFactory;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\Support\PostgresSubstitutionHarness;
use Tests\TestCase;

final class E2ELiveMutationGuardTest extends TestCase
{
    public function test_every_mutation_window_and_reconnector_refuses_a_substitute(): void
    {
        $harness = PostgresSubstitutionHarness::fromEnvironment();
        $migrate = [PHP_BINARY, 'artisan', 'migrate', '--database=e2e', '--force', '--no-interaction'];
        $seed = [PHP_BINARY, 'artisan', 'db:seed', '--database=e2e', '--class=Database\\Seeders\\E2ESeeder', '--force', '--no-interaction'];

        $beforeMigrate = $harness->candidate();
        $migrateCanary = $harness->candidate();
        $migrateFingerprint = $harness->installCanary($migrateCanary);
        $this->assertRefused($harness->runChild($beforeMigrate, $migrate, 'provisioning-migrate', $migrateCanary));
        self::assertSame($migrateFingerprint, $harness->canaryFingerprint($migrateCanary));
        self::assertFalse($harness->activationExists($migrateCanary));

        $beforeSeed = $harness->candidate();
        $seedCanary = $harness->candidate();
        $seedFingerprint = $harness->installCanary($seedCanary);
        self::assertTrue($harness->runChild($beforeSeed, $migrate, 'provisioning-migrate')->isSuccessful());
        $this->assertRefused($harness->runChild($beforeSeed, $seed, 'provisioning-seed', $seedCanary));
        self::assertSame($seedFingerprint, $harness->canaryFingerprint($seedCanary));
        self::assertFalse($harness->activationExists($seedCanary));

        $wrongNonce = $harness->candidate();
        foreach ([['provisioning-migrate', $migrate], ['provisioning-seed', $seed]] as [$phase, $command]) {
            $this->assertRefused($harness->runChild(
                $wrongNonce,
                $command,
                $phase,
                null,
                ['P00_PG_INSTANCE_NONCE_SHA256' => str_repeat('0', 64)],
            ));
        }

        $happyPath = $harness->candidate();
        self::assertTrue($harness->runChild($happyPath, $migrate, 'provisioning-migrate')->isSuccessful());
        self::assertTrue($harness->runChild($happyPath, $seed, 'provisioning-seed')->isSuccessful());
        $happyPath->activate();
        $happyPath->assertActive();
        self::assertTrue($harness->activationExists($happyPath, true));
        self::assertTrue($harness->runChild(
            $happyPath,
            [PHP_BINARY, 'artisan', 'env', '--no-interaction'],
            'active',
        )->isSuccessful());

        $activationExpected = $harness->candidate();
        $activationCanary = $harness->candidate();
        $activationFingerprint = $harness->installCanary($activationCanary);
        $expected = $activationExpected->environment('provisioning-migrate');
        try {
            PdoE2eSupervisor::activateVerifiedPdo(
                $harness->pdo($activationCanary),
                $expected['P00_E2E_DATABASE'],
                $expected['P00_E2E_ROLE'],
                $expected['P00_PG_INSTANCE_NONCE_SHA256'],
                $expected['P00_E2E_ACTIVATION_NONCE_SHA256'],
                $expected['P00_E2E_FIXTURE_CONTRACT_SHA256'],
            );
            self::fail('Activation substitution was accepted.');
        } catch (RuntimeException $error) {
            self::assertSame('P00_E2E_LIVE_PDO_REFUSED', $error->getMessage());
        }
        self::assertSame($activationFingerprint, $harness->canaryFingerprint($activationCanary));
        self::assertFalse($harness->activationExists($activationCanary));

        $sealed = $harness->candidate();
        $sealedFingerprint = $harness->installCanary($sealed);
        $profile = $harness->profile($sealed);
        $connection = app(ConnectionFactory::class)->make($profile->laravelConfiguration(), 'e2e-live-guard');
        $profile->assertLaravelConfiguration($connection, 'e2e-live-guard');
        $pdo = $connection->getPdo();
        $sealedFacts = $sealed->environment('provisioning-migrate');
        E2eDatabaseLease::assertBootConnection(
            $pdo,
            $sealedFacts['P00_E2E_DATABASE'],
            $sealedFacts['P00_E2E_ROLE'],
            $sealedFacts['P00_PG_INSTANCE_NONCE_SHA256'],
            $sealedFacts['P00_E2E_ACTIVATION_NONCE_SHA256'],
            $sealedFacts['P00_E2E_FIXTURE_CONTRACT_SHA256'],
            'provisioning-migrate',
        );
        PostgresConnectionProfile::sealVerifiedPdo($connection, $pdo);
        $backendPid = (int) $connection->scalar('SELECT pg_backend_pid()');
        $harness->terminateBackend($sealed, $backendPid);
        $this->expectMarker(
            'P00_RECONNECT_REFUSED',
            static fn () => $connection->statement('CREATE TABLE forbidden_lost_write (id integer)'),
        );
        self::assertSame($sealedFingerprint, $harness->canaryFingerprint($sealed));

        $replacement = $harness->candidate();
        $replacementFingerprint = $harness->installCanary($replacement);
        $connection->setPdo($harness->pdo($replacement));
        $this->expectMarker(
            'P00_PDO_SUBSTITUTION_REFUSED',
            static fn () => $connection->statement('CREATE TABLE forbidden_write (id integer)'),
        );
        self::assertSame($replacementFingerprint, $harness->canaryFingerprint($replacement));
    }

    private function assertRefused(Process $process): void
    {
        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('P00_E2E_LIVE_PDO_REFUSED', $process->getOutput().$process->getErrorOutput());
    }

    private function expectMarker(string $marker, callable $callback): void
    {
        try {
            $callback();
            self::fail($marker.' was not raised.');
        } catch (RuntimeException $error) {
            self::assertSame($marker, $error->getMessage());
        }
    }
}
