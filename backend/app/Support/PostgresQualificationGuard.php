<?php

namespace App\Support;

use PDO;
use RuntimeException;
use Throwable;

final class PostgresQualificationGuard
{
    private static ?PostgresConnectionProfile $bootstrapProfile = null;

    private static ?array $bootstrapAuthority = null;

    public static function assertPdo(
        PDO $pdo,
        PostgresConnectionProfile $profile,
        string $nonceSha256,
    ): array {
        $live = $pdo->query(
            "SELECT current_database() AS database, current_user AS role,
            current_setting('server_version_num')::int AS server_version_num,
            current_setting('dorzak.instance_nonce_sha256') AS instance_nonce_sha256,
            inet_server_addr()::text AS server_address,
            inet_server_port() AS server_port,
            COALESCE((SELECT ssl FROM pg_stat_ssl WHERE pid = pg_backend_pid()), false) AS tls",
        )->fetch(PDO::FETCH_ASSOC);
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql' || ! is_array($live)
            || $live['database'] !== $profile->database || $live['role'] !== $profile->username
            || (int) $live['server_version_num'] < 160000 || (int) $live['server_version_num'] >= 170000
            || ! preg_match('/^[0-9a-f]{64}$/', $nonceSha256)
            || ! hash_equals($nonceSha256, (string) $live['instance_nonce_sha256'])) {
            throw new RuntimeException('P00_PG_LIVE_PDO_REFUSED');
        }

        return $live;
    }

    public static function fingerprint(array $live): string
    {
        $facts = [
            'database' => (string) ($live['database'] ?? ''),
            'role' => (string) ($live['role'] ?? ''),
            'serverVersionNum' => (int) ($live['server_version_num'] ?? 0),
            'instanceNonceSha256' => (string) ($live['instance_nonce_sha256'] ?? ''),
            'serverAddress' => (string) ($live['server_address'] ?? ''),
            'serverPort' => (int) ($live['server_port'] ?? 0),
            'tls' => (bool) ($live['tls'] ?? false),
        ];
        if ($facts['database'] === '' || $facts['role'] === ''
            || ! preg_match('/^[0-9a-f]{64}$/', $facts['instanceNonceSha256'])
            || $facts['serverAddress'] === '' || $facts['serverPort'] < 1) {
            throw new RuntimeException('P00_PG_LIVE_FINGERPRINT_REFUSED');
        }

        return hash('sha256', json_encode($facts, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    public static function bindBootstrapAuthority(
        PostgresConnectionProfile $profile,
        string $identity,
        string $attestationSha256,
        string $nonceSha256,
        string $qualificationNonceSha256,
        array $live,
    ): void {
        if (self::$bootstrapProfile !== null
            || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9._:@#\/-]{0,255}$/', $identity)
            || ! preg_match('/^[0-9a-f]{64}$/', $attestationSha256)
            || ! preg_match('/^[0-9a-f]{64}$/', $nonceSha256)
            || ! preg_match('/^[0-9a-f]{64}$/', $qualificationNonceSha256)) {
            throw new RuntimeException('P00_PG_BOOTSTRAP_BIND_REFUSED');
        }
        self::$bootstrapProfile = $profile;
        $liveFingerprintSha256 = self::fingerprint($live);
        self::$bootstrapAuthority = compact(
            'identity', 'attestationSha256', 'nonceSha256',
            'qualificationNonceSha256', 'liveFingerprintSha256',
        );
    }

    public static function assertBootstrapAuthority(
        string $url,
        string $identity,
        string $attestationSha256,
        string $nonceSha256,
        string $qualificationNonceSha256,
    ): PostgresConnectionProfile {
        if (self::$bootstrapProfile === null || self::$bootstrapAuthority === null) {
            throw new RuntimeException('P00_PG_BOOTSTRAP_AUTHORITY_REFUSED');
        }
        self::$bootstrapProfile->assertSameAuthority(PostgresConnectionProfile::fromUrl($url));
        $expected = self::$bootstrapAuthority;
        if (! hash_equals($expected['identity'], $identity)
            || ! hash_equals($expected['attestationSha256'], $attestationSha256)
            || ! hash_equals($expected['nonceSha256'], $nonceSha256)
            || ! hash_equals($expected['qualificationNonceSha256'], $qualificationNonceSha256)) {
            throw new RuntimeException('P00_PG_BOOTSTRAP_AUTHORITY_REFUSED');
        }

        return self::$bootstrapProfile;
    }

    public static function assertBootstrapLive(array $live): void
    {
        $expected = self::$bootstrapAuthority['liveFingerprintSha256'] ?? null;
        $actual = self::fingerprint($live);
        if (! is_string($expected) || ! hash_equals($expected, $actual)) {
            throw new RuntimeException('P00_PG_BOOTSTRAP_LIVE_REFUSED');
        }
    }

    public static function assertEmptySchema(PDO $pdo): void
    {
        $count = (int) $pdo->query(
            "WITH user_objects AS (
                SELECT c.oid FROM pg_class c
                JOIN pg_namespace n ON n.oid = c.relnamespace
                WHERE n.nspname NOT IN ('pg_catalog', 'information_schema')
                  AND n.nspname !~ '^pg_toast'
                  AND c.relkind IN ('r', 'p', 'v', 'm', 'S', 'f')
                UNION ALL
                SELECT t.oid FROM pg_type t
                JOIN pg_namespace n ON n.oid = t.typnamespace
                WHERE n.nspname NOT IN ('pg_catalog', 'information_schema')
                  AND n.nspname !~ '^pg_toast'
                  AND t.typtype IN ('c', 'd', 'e', 'r', 'm')
                UNION ALL
                SELECT p.oid FROM pg_proc p
                JOIN pg_namespace n ON n.oid = p.pronamespace
                WHERE n.nspname NOT IN ('pg_catalog', 'information_schema')
                  AND n.nspname !~ '^pg_toast'
                UNION ALL
                SELECT n.oid FROM pg_namespace n
                WHERE n.nspname NOT IN ('public', 'pg_catalog', 'information_schema')
                  AND n.nspname !~ '^pg_toast'
            ) SELECT count(*) FROM user_objects",
        )->fetchColumn();
        if ($count !== 0) {
            throw new RuntimeException('P00_PG_NONEMPTY_CANDIDATE_REFUSED');
        }
    }

    public static function activateCandidate(PDO $pdo, string $qualificationNonceSha256): void
    {
        if (! preg_match('/^[0-9a-f]{64}$/', $qualificationNonceSha256)) {
            throw new RuntimeException('P00_PG_QUALIFICATION_ACTIVATION_REFUSED');
        }
        try {
            $pdo->beginTransaction();
            $pdo->exec(
                'CREATE TABLE p00_qualification_activation (
                    singleton boolean PRIMARY KEY CHECK (singleton),
                    qualification_nonce_sha256 char(64) NOT NULL
                )',
            );
            $statement = $pdo->prepare(
                'INSERT INTO p00_qualification_activation
                 (singleton, qualification_nonce_sha256) VALUES (true, :nonce)',
            );
            $statement->execute(['nonce' => $qualificationNonceSha256]);
            $pdo->commit();
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException('P00_PG_QUALIFICATION_ACTIVATION_REFUSED', 0, $error);
        }
    }

    public static function assertActivated(PDO $pdo, string $qualificationNonceSha256): void
    {
        try {
            $rows = $pdo->query(
                'SELECT qualification_nonce_sha256
                 FROM p00_qualification_activation WHERE singleton = true',
            )->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $error) {
            throw new RuntimeException('P00_PG_QUALIFICATION_ACTIVATION_REFUSED', 0, $error);
        }
        if (count($rows) !== 1 || ! preg_match('/^[0-9a-f]{64}$/', $qualificationNonceSha256)
            || ! hash_equals($qualificationNonceSha256, (string) $rows[0])) {
            throw new RuntimeException('P00_PG_QUALIFICATION_ACTIVATION_REFUSED');
        }
    }
}
