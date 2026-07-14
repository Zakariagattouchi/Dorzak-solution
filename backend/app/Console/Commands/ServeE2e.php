<?php

namespace App\Console\Commands;

use App\Support\E2eDatabaseLease;
use App\Support\E2eProvisioningException;
use App\Support\PdoE2eSupervisor;
use Database\Seeders\E2ESeeder;
use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

final class ServeE2e extends Command
{
    protected $signature = 'e2e:serve {--host=127.0.0.1} {--port=8000}';

    protected $description = 'Create and serve one attested PostgreSQL E2E capability';

    public function handle(): int
    {
        $lease = null;
        try {
            $host = (string) $this->option('host');
            $port = filter_var($this->option('port'), FILTER_VALIDATE_INT);
            if ($host !== '127.0.0.1' || $port === false || $port < 1024 || $port > 65535) {
                throw new RuntimeException('E2E listener must be a non-privileged loopback port.');
            }

            $attestationPath = (string) env('P00_E2E_SERVICE_ATTESTATION_PATH');
            $attestationSha256 = (string) env('P00_E2E_SERVICE_ATTESTATION_SHA256');
            $attestation = E2eDatabaseLease::attestationFromFile($attestationPath, $attestationSha256);
            $supervisor = new PdoE2eSupervisor(
                (string) env('P00_E2E_SUPERVISOR_DB_URL'),
                (string) env('P00_PG_IDENTITY'),
            );
            $lease = E2eDatabaseLease::acquire(
                $this->laravel->environment(),
                $supervisor,
                $attestation,
                (string) env('P00_PG_IDENTITY'),
                (string) env('P00_PG_INSTANCE_NONCE_SHA256'),
                (string) env('P00_E2E_SERVICE_LIFECYCLE_ID'),
                hash('sha256', E2ESeeder::CONTRACT_JSON),
            );

            self::prepare($lease, static function (array $command, array $environment): void {
                (new Process(
                    $command,
                    base_path(),
                    self::childEnvironment($environment),
                ))->setTimeout(300)->mustRun();
            });

            $environment = self::childEnvironment($lease->environment('active'));
            $server = new Process(
                [PHP_BINARY, 'artisan', 'serve', '--host=127.0.0.1', "--port={$port}", '--no-interaction'],
                base_path(),
                $environment,
            );
            $server->setTimeout(null);
            $this->info("E2E_SERVE PASS database={$lease->database()} lifecycle={$lease->lifecycleId()}");
            $serverStatus = $server->run(static function (string $type, string $buffer): void {
                fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
            });
            if ($serverStatus !== self::SUCCESS) {
                throw new RuntimeException('E2E server exited unsuccessfully.');
            }

            return self::SUCCESS;
        } catch (E2eProvisioningException $error) {
            $this->error("E2E_SERVE REFUSED orphan_database={$error->database} orphan_role={$error->role} lifecycle={$error->lifecycleId}");

            return self::FAILURE;
        } catch (Throwable) {
            $database = $lease?->database() ?? 'none';
            $role = $lease?->role() ?? 'none';
            $lifecycle = $lease?->lifecycleId() ?? (string) env('P00_E2E_SERVICE_LIFECYCLE_ID', 'unknown');
            $this->error("E2E_SERVE REFUSED orphan_database={$database} orphan_role={$role} lifecycle={$lifecycle}");

            return self::FAILURE;
        }
    }

    public static function prepare(E2eDatabaseLease $lease, callable $runner): void
    {
        $runner(
            [PHP_BINARY, 'artisan', 'migrate', '--database=e2e', '--force', '--no-interaction'],
            $lease->environment('provisioning-migrate'),
        );
        $runner(
            [PHP_BINARY, 'artisan', 'db:seed', '--database=e2e', '--class=Database\\Seeders\\E2ESeeder', '--force', '--no-interaction'],
            $lease->environment('provisioning-seed'),
        );
        $lease->activate();
    }

    public static function childEnvironment(array $capabilityEnvironment, array $overrides = []): array
    {
        return array_merge(
            $_SERVER,
            $_ENV,
            $capabilityEnvironment,
            $overrides,
            ['P00_E2E_SUPERVISOR_DB_URL' => false],
        );
    }
}
