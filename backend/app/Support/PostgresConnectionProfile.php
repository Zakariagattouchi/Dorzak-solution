<?php

namespace App\Support;

use Illuminate\Database\Connection;
use PDO;
use RuntimeException;
use ValueError;

final class PostgresConnectionProfile
{
    private const SSLMODES = ['disable', 'allow', 'prefer', 'require', 'verify-ca', 'verify-full'];

    private function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $database,
        public readonly string $username,
        private readonly string $password,
        public readonly string $sslmode,
    ) {}

    public static function fromUrl(string $url, bool $requireTestSuffix = true): self
    {
        if ($url === '' || preg_match('/[\x00-\x20\x7f;]/', $url)) {
            throw new RuntimeException('PostgreSQL URL contains an unsafe byte.');
        }

        try {
            $parts = parse_url($url);
        } catch (ValueError) {
            throw new RuntimeException('PostgreSQL URL cannot be parsed.');
        }

        if (! is_array($parts)
            || ! in_array($parts['scheme'] ?? null, ['postgres', 'postgresql'], true)
            || ! isset($parts['host'], $parts['user'], $parts['pass'], $parts['path'])
            || isset($parts['fragment'])
            || ! is_int($parts['port'] ?? 5432)) {
            throw new RuntimeException('PostgreSQL URL authority is incomplete.');
        }

        $decode = static function (string $value): string {
            if (preg_match('/%(?![0-9A-Fa-f]{2})/', $value)) {
                throw new RuntimeException('PostgreSQL URL encoding is invalid.');
            }
            $decoded = rawurldecode($value);
            if ($decoded === '' || preg_match('/[\x00-\x20\x7f;=]/', $decoded)) {
                throw new RuntimeException('PostgreSQL URL component is unsafe.');
            }

            return $decoded;
        };

        $host = trim((string) $parts['host'], '[]');
        $isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
        $isDns = preg_match('/^(?=.{1,253}$)(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)(?:\.(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?))*$/', $host) === 1;
        if (! $isIp && ! $isDns) {
            throw new RuntimeException('PostgreSQL host is unsafe.');
        }

        $port = $parts['port'] ?? 5432;
        if ($port < 1 || $port > 65535 || ! str_starts_with($parts['path'], '/')) {
            throw new RuntimeException('PostgreSQL port or path is invalid.');
        }

        $database = $decode(substr($parts['path'], 1));
        $username = $decode($parts['user']);
        $password = $decode($parts['pass']);
        if (! preg_match('/^[a-z][a-z0-9_]{1,62}$/', $database)
            || ! preg_match('/^[a-z][a-z0-9_]{1,62}$/', $username)
            || ($requireTestSuffix && ! str_ends_with($database, '_test'))) {
            throw new RuntimeException('PostgreSQL database or role is outside the P00 grammar.');
        }

        $sslmode = 'prefer';
        if (($parts['query'] ?? '') !== '') {
            $items = explode('&', $parts['query']);
            if (count($items) !== 1) {
                throw new RuntimeException('PostgreSQL URL options are not closed.');
            }
            $pair = explode('=', $items[0], 2);
            if (count($pair) !== 2 || $decode($pair[0]) !== 'sslmode') {
                throw new RuntimeException('PostgreSQL URL option is not allowed.');
            }
            $sslmode = $decode($pair[1]);
        }
        if (! in_array($sslmode, self::SSLMODES, true)) {
            throw new RuntimeException('PostgreSQL sslmode is unsupported.');
        }

        return new self($host, $port, $database, $username, $password, $sslmode);
    }

    public function pdo(): PDO
    {
        $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database};sslmode={$this->sslmode}";

        return new PDO($dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
    }

    public function laravelConfiguration(): array
    {
        return [
            'driver' => 'pgsql', 'host' => $this->host, 'port' => $this->port,
            'database' => $this->database, 'username' => $this->username,
            'password' => $this->password, 'charset' => 'utf8', 'prefix' => '',
            'prefix_indexes' => true, 'search_path' => 'public', 'sslmode' => $this->sslmode,
        ];
    }

    public function assertSameAuthority(self $other): void
    {
        if ($this->host !== $other->host || $this->port !== $other->port
            || $this->database !== $other->database || $this->username !== $other->username
            || $this->sslmode !== $other->sslmode || ! hash_equals($this->password, $other->password)) {
            throw new RuntimeException('P00_PG_BOOTSTRAP_AUTHORITY_REFUSED');
        }
    }

    public function assertLaravelConfiguration(Connection $connection, string $expectedName): void
    {
        $actual = $connection->getConfig();
        foreach (['read', 'write', 'sticky', 'unix_socket', 'options'] as $forbidden) {
            if (array_key_exists($forbidden, $actual)) {
                throw new RuntimeException('PostgreSQL read/write or option override is prohibited.');
            }
        }

        $actualHost = trim((string) ($actual['host'] ?? ''), '[]');
        $actualPort = filter_var($actual['port'] ?? null, FILTER_VALIDATE_INT);
        if ($actualHost !== $this->host || $actualPort !== $this->port) {
            throw new RuntimeException('PostgreSQL Laravel transport mismatch.');
        }

        $expected = [
            'driver' => 'pgsql', 'database' => $this->database, 'username' => $this->username,
            'sslmode' => $this->sslmode, 'charset' => 'utf8', 'prefix' => '',
            'prefix_indexes' => true, 'search_path' => 'public',
        ];
        foreach ($expected as $key => $value) {
            if (($actual[$key] ?? null) !== $value) {
                throw new RuntimeException('PostgreSQL Laravel configuration mismatch.');
            }
        }
        if ($connection->getName() !== $expectedName
            || ! isset($actual['password'])
            || ! hash_equals($this->password, (string) $actual['password'])) {
            throw new RuntimeException('PostgreSQL Laravel authority mismatch.');
        }
    }

    public static function sealVerifiedPdo(Connection $connection, PDO $verifiedPdo): void
    {
        $assertSame = static function (Connection $current) use ($verifiedPdo): void {
            if ($current->getRawPdo() !== $verifiedPdo) {
                throw new RuntimeException('P00_PDO_SUBSTITUTION_REFUSED');
            }
        };
        $assertSame($connection);
        $connection->beforeExecuting(
            static function (string $query, array $bindings, Connection $current) use ($assertSame): void {
                $assertSame($current);
            },
        );
        $connection->beforeStartingTransaction($assertSame);
        $connection->setReconnector(
            static fn (Connection $current): never => throw new RuntimeException('P00_RECONNECT_REFUSED'),
        );
    }
}
