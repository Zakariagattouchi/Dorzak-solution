<?php

namespace Tests\LivePostgres;

use App\Support\PostgresConnectionProfile;
use App\Support\PostgresQualificationGuard;
use Illuminate\Database\Connectors\ConnectionFactory;
use RuntimeException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use Tests\Support\PostgresSubstitutionHarness;
use Tests\TestCase;

final class PostgresQualificationLiveGuardTest extends TestCase
{
    public function test_bootstrap_authority_reconnect_and_pdo_replacement_fail_closed(): void
    {
        $harness = PostgresSubstitutionHarness::fromEnvironment();
        $expected = $harness->candidate();
        $qualificationNonceSha256 = hash('sha256', random_bytes(32));
        $harness->activateQualification($expected, $qualificationNonceSha256);
        $decoy = $harness->candidate();
        $canary = $harness->installCanary($decoy);
        $decoyUrl = $decoy->environment('provisioning-migrate')['P00_E2E_DB_URL'];
        $substitutePath = tempnam(sys_get_temp_dir(), 'p00-pg-substitute-');
        self::assertIsString($substitutePath);
        try {
            self::assertTrue(chmod($substitutePath, 0600));
            self::assertSame(strlen($decoyUrl), file_put_contents($substitutePath, $decoyUrl, LOCK_EX));
            $input = new InputStream;
            $barrierOutput = '';
            $expectedUrl = $expected->environment('provisioning-migrate')['P00_E2E_DB_URL'];
            $process = new Process(
                [
                    PHP_BINARY, 'vendor/bin/phpunit', '-c', 'phpunit.pgsql.xml',
                    'tests/LivePostgres/QualificationBootstrapProbeTest.php',
                ],
                base_path(),
                array_merge($_SERVER, $_ENV, [
                    'DB_URL' => $expectedUrl,
                    'P00_PG_SCHEMA_READY' => '1',
                    'P00_PG_QUALIFIED_CANDIDATE' => '1',
                    'P00_PG_QUALIFICATION_NONCE_SHA256' => $qualificationNonceSha256,
                    'P00_PG_TEST_SUBSTITUTE_URL_PATH' => $substitutePath,
                ]),
            );
            $process->setInput($input);
            $process->setTimeout(60);
            $process->start();
            self::assertTrue($process->waitUntil(
                static function (string $type, string $buffer) use (&$barrierOutput): bool {
                    if ($type === Process::OUT) {
                        $barrierOutput .= $buffer;
                    }

                    return str_contains($barrierOutput, "P00_PG_BOOTSTRAP_BARRIER READY\n");
                },
            ));
            $input->write("GO\n");
            $input->close();
            $process->wait();
            self::assertFalse($process->isSuccessful());
            self::assertStringContainsString(
                'P00_PG_BOOTSTRAP_AUTHORITY_REFUSED',
                $process->getOutput().$process->getErrorOutput(),
            );
        } finally {
            if (file_exists($substitutePath) || is_link($substitutePath)) {
                self::assertTrue(unlink($substitutePath));
            }
        }
        self::assertSame($canary, $harness->canaryFingerprint($decoy));
        self::assertFalse($harness->activationExists($expected));

        $lost = $harness->candidate();
        $lostFingerprint = $harness->installCanary($lost);
        $profile = $harness->profile($lost);
        $connection = app(ConnectionFactory::class)->make(
            $profile->laravelConfiguration(), 'qualification-live-guard',
        );
        $profile->assertLaravelConfiguration($connection, 'qualification-live-guard');
        $pdo = $connection->getPdo();
        PostgresQualificationGuard::assertPdo(
            $pdo, $profile, (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
        );
        PostgresConnectionProfile::sealVerifiedPdo($connection, $pdo);
        $backendPid = (int) $connection->scalar('SELECT pg_backend_pid()');
        $harness->terminateBackend($lost, $backendPid);
        $this->expectMarker(
            'P00_RECONNECT_REFUSED',
            static fn () => $connection->statement('CREATE TABLE forbidden_qualification_lost_write (id integer)'),
        );
        self::assertSame($lostFingerprint, $harness->canaryFingerprint($lost));

        $connection->setPdo($harness->pdo($decoy));
        $this->expectMarker(
            'P00_PDO_SUBSTITUTION_REFUSED',
            static fn () => $connection->statement('CREATE TABLE forbidden_qualification_replacement (id integer)'),
        );
        self::assertSame($canary, $harness->canaryFingerprint($decoy));
    }

    private function expectMarker(string $marker, callable $operation): void
    {
        try {
            $operation();
            self::fail($marker.' was not raised.');
        } catch (RuntimeException $error) {
            self::assertSame($marker, $error->getMessage());
        }
    }
}
