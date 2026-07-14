<?php

namespace App\Support;

use PDO;
use RuntimeException;
use Throwable;

final class PdoE2eSupervisor implements E2eSupervisor
{
    private PDO $pdo;

    public function __construct(
        private readonly string $supervisorUrl,
        private readonly string $identity,
    ) {
        $this->pdo = self::connect($supervisorUrl);
    }

    public function facts(): array
    {
        $row = $this->pdo->query(
            "SELECT current_database() AS database, current_user AS role,
            current_setting('server_version_num')::int / 10000 AS server_major,
            current_setting('dorzak.instance_nonce_sha256') AS instance_nonce_sha256",
        )->fetch(PDO::FETCH_ASSOC);

        return [
            'driver' => $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'identity' => $this->identity,
            'serverMajor' => (int) $row['server_major'],
            'instanceNonceSha256' => $row['instance_nonce_sha256'],
            'database' => $row['database'],
            'role' => $row['role'],
        ];
    }

    public function candidateExists(string $database, string $role): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT EXISTS(SELECT 1 FROM pg_database WHERE datname = :database)
             OR EXISTS(SELECT 1 FROM pg_roles WHERE rolname = :role)',
        );
        $statement->execute(['database' => $database, 'role' => $role]);

        return (bool) $statement->fetchColumn();
    }

    public function noncandidateFingerprint(): string
    {
        $rows = $this->pdo->query(
            "SELECT 'database' AS kind, datname AS name, datallowconn::text AS value
             FROM pg_database WHERE datname NOT LIKE 'p00_e2e_%'
             UNION ALL
             SELECT 'role', rolname, concat_ws(':', rolsuper, rolcreatedb, rolcreaterole, rolreplication, rolbypassrls)
             FROM pg_roles WHERE rolname NOT LIKE 'p00_e2e_r_%'
             ORDER BY kind, name",
        )->fetchAll(PDO::FETCH_ASSOC);

        return hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    public function createCandidate(string $database, string $role, string $password): void
    {
        $databaseIdentifier = self::identifier($database);
        $roleIdentifier = self::identifier($role);
        $passwordLiteral = $this->pdo->quote($password);
        $this->pdo->exec(
            "CREATE ROLE {$roleIdentifier} LOGIN PASSWORD {$passwordLiteral}
             NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT NOREPLICATION NOBYPASSRLS",
        );
        $this->pdo->exec(
            "CREATE DATABASE {$databaseIdentifier} OWNER {$roleIdentifier} TEMPLATE template0 ENCODING 'UTF8'",
        );
        $this->pdo->exec("REVOKE ALL ON DATABASE {$databaseIdentifier} FROM PUBLIC");
        $this->pdo->exec("GRANT CONNECT, TEMPORARY ON DATABASE {$databaseIdentifier} TO {$roleIdentifier}");
    }

    public function candidateUrl(string $database, string $role, string $password): string
    {
        $profile = PostgresConnectionProfile::fromUrl($this->supervisorUrl, false);
        $host = str_contains($profile->host, ':') ? '['.$profile->host.']' : $profile->host;
        $query = '?sslmode='.rawurlencode($profile->sslmode);

        return sprintf(
            'postgresql://%s:%s@%s:%d/%s%s',
            rawurlencode($role),
            rawurlencode($password),
            $host,
            $profile->port,
            rawurlencode($database),
            $query,
        );
    }

    public function noncandidateDatabasesWithAccess(string $role, string $candidate): array
    {
        $statement = $this->pdo->prepare(
            "SELECT datname FROM pg_database
             WHERE datname <> :candidate AND datallowconn
               AND has_database_privilege(:role, datname, 'CONNECT')
             ORDER BY datname",
        );
        $statement->execute(['candidate' => $candidate, 'role' => $role]);

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function candidateFacts(string $url): array
    {
        $pdo = self::connect($url);
        $row = $pdo->query(
            "SELECT current_database() AS database, current_user AS role,
            current_setting('server_version_num')::int / 10000 AS server_major,
            current_setting('dorzak.instance_nonce_sha256') AS instance_nonce_sha256,
            CASE WHEN rolsuper THEN 1 ELSE 0 END AS rolsuper,
            CASE WHEN rolcreatedb THEN 1 ELSE 0 END AS rolcreatedb,
            CASE WHEN rolcreaterole THEN 1 ELSE 0 END AS rolcreaterole,
            CASE WHEN rolbypassrls THEN 1 ELSE 0 END AS rolbypassrls,
            CASE WHEN rolreplication THEN 1 ELSE 0 END AS rolreplication
            FROM pg_roles WHERE rolname = current_user",
        )->fetch(PDO::FETCH_ASSOC);
        $activation = ['activation_nonce_sha256' => null, 'fixture_contract_sha256' => null];
        if ($pdo->query("SELECT to_regclass('public.p00_e2e_activation')")->fetchColumn() !== null) {
            $activation = $pdo->query(
                'SELECT activation_nonce_sha256, fixture_contract_sha256
                 FROM p00_e2e_activation WHERE singleton = true',
            )->fetch(PDO::FETCH_ASSOC);
        }

        return [
            'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'database' => $row['database'],
            'role' => $row['role'],
            'serverMajor' => (int) $row['server_major'],
            'instanceNonceSha256' => $row['instance_nonce_sha256'],
            'superuser' => (int) $row['rolsuper'] === 1,
            'createdb' => (int) $row['rolcreatedb'] === 1,
            'createrole' => (int) $row['rolcreaterole'] === 1,
            'bypassrls' => (int) $row['rolbypassrls'] === 1,
            'replication' => (int) $row['rolreplication'] === 1,
            'activationNonceSha256' => $activation['activation_nonce_sha256'],
            'fixtureContractSha256' => $activation['fixture_contract_sha256'],
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
        $pdo = PostgresConnectionProfile::fromUrl($url)->pdo();
        self::activateVerifiedPdo(
            $pdo,
            $database,
            $role,
            $serviceNonceSha256,
            $activationNonceSha256,
            $fixtureContractSha256,
        );
    }

    public static function activateVerifiedPdo(
        PDO $pdo,
        string $database,
        string $role,
        string $serviceNonceSha256,
        string $activationNonceSha256,
        string $fixtureContractSha256,
    ): void {
        try {
            E2eDatabaseLease::assertBootConnection(
                $pdo,
                $database,
                $role,
                $serviceNonceSha256,
                $activationNonceSha256,
                $fixtureContractSha256,
                'provisioning-activate',
            );
            $pdo->beginTransaction();
            $pdo->exec(
                'CREATE TABLE p00_e2e_activation (
                    singleton boolean PRIMARY KEY CHECK (singleton),
                    activation_nonce_sha256 char(64) NOT NULL,
                    fixture_contract_sha256 char(64) NOT NULL,
                    service_nonce_sha256 char(64) NOT NULL
                )',
            );
            $statement = $pdo->prepare(
                'INSERT INTO p00_e2e_activation
                 (singleton, activation_nonce_sha256, fixture_contract_sha256, service_nonce_sha256)
                 VALUES (true, :activation, :contract, :service)',
            );
            $statement->execute([
                'activation' => $activationNonceSha256,
                'contract' => $fixtureContractSha256,
                'service' => $serviceNonceSha256,
            ]);
            E2eDatabaseLease::assertBootConnection(
                $pdo,
                $database,
                $role,
                $serviceNonceSha256,
                $activationNonceSha256,
                $fixtureContractSha256,
                'active',
            );
            $pdo->commit();
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $error;
        }
    }

    public function terminateBackend(string $database, string $role, int $pid): void
    {
        if ($pid < 1) {
            throw new RuntimeException('P00_BACKEND_TERMINATION_REFUSED');
        }
        $statement = $this->pdo->prepare(
            'SELECT pg_terminate_backend(pid) AS terminated
             FROM pg_stat_activity
             WHERE pid = :pid AND datname = :database AND usename = :role
               AND pid <> pg_backend_pid()',
        );
        $statement->execute(['pid' => $pid, 'database' => $database, 'role' => $role]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) !== 1 || $rows[0]['terminated'] !== true) {
            throw new RuntimeException('P00_BACKEND_TERMINATION_REFUSED');
        }
    }

    private static function connect(string $url): PDO
    {
        return PostgresConnectionProfile::fromUrl($url, false)->pdo();
    }

    private static function identifier(string $value): string
    {
        if (! preg_match('/^[a-z][a-z0-9_]{1,62}$/', $value)) {
            throw new RuntimeException('Unsafe PostgreSQL identifier.');
        }

        return '"'.$value.'"';
    }
}
