<?php

declare(strict_types=1);

use App\Support\PostgresConnectionProfile;
use App\Support\PostgresQualificationGuard;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$fail = static function (string $message): never {
    fwrite(STDERR, "P00_POSTGRES_GUARD FAIL {$message}\n");
    exit(2);
};

$url = getenv('DB_URL') ?: '';
if (! extension_loaded('pdo_pgsql')) {
    $fail('pdo_pgsql is unavailable');
}

$attestationPath = getenv('P00_PG_ATTESTATION_PATH') ?: '';
$attestationSha256 = getenv('P00_PG_ATTESTATION_SHA256') ?: '';
$identity = getenv('P00_PG_IDENTITY') ?: '';
$nonceSha256 = getenv('P00_PG_INSTANCE_NONCE_SHA256') ?: '';
$qualificationNonceSha256 = getenv('P00_PG_QUALIFICATION_NONCE_SHA256') ?: '';
if (getenv('P00_PG_QUALIFIED_CANDIDATE') !== '1') {
    $fail('qualification runner marker is absent');
}
if (! is_file($attestationPath) || is_link($attestationPath) || realpath($attestationPath) !== $attestationPath
    || ! preg_match('/^[0-9a-f]{64}$/', $attestationSha256)
    || ! hash_equals($attestationSha256, hash_file('sha256', $attestationPath))) {
    $fail('approved PostgreSQL attestation is absent or changed');
}
try {
    $attestation = json_decode((string) file_get_contents($attestationPath), true, 512, JSON_THROW_ON_ERROR);
    if (array_keys($attestation) !== ['schemaVersion', 'kind', 'identity', 'serverMajor', 'immutable', 'instanceNonceSha256']
        || $attestation['schemaVersion'] !== 2
        || $attestation['identity'] !== $identity
        || $attestation['serverMajor'] !== 16
        || $attestation['immutable'] !== true
        || $attestation['instanceNonceSha256'] !== $nonceSha256) {
        throw new RuntimeException('approved PostgreSQL identity is invalid');
    }
    $profile = PostgresConnectionProfile::fromUrl($url);
    $pdo = $profile->pdo();
    $live = PostgresQualificationGuard::assertPdo($pdo, $profile, $nonceSha256);
    PostgresQualificationGuard::assertActivated($pdo, $qualificationNonceSha256);
    PostgresQualificationGuard::bindBootstrapAuthority(
        $profile, $identity, $attestationSha256, $nonceSha256,
        $qualificationNonceSha256, $live,
    );
    $substitutePath = getenv('P00_PG_TEST_SUBSTITUTE_URL_PATH') ?: '';
    if ($substitutePath !== '') {
        $handle = null;
        $opened = null;
        try {
            if ((getenv('APP_ENV') ?: '') !== 'testing' || ! function_exists('posix_geteuid')) {
                throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
            }
            $handle = @fopen($substitutePath, 'rb');
            $opened = is_resource($handle) ? fstat($handle) : false;
            $named = @lstat($substitutePath);
            if (! is_resource($handle) || ! is_array($opened) || ! is_array($named)
                || ($opened['mode'] & 0170000) !== 0100000
                || ($opened['mode'] & 0777) !== 0600
                || $opened['uid'] !== posix_geteuid()
                || $opened['dev'] !== $named['dev'] || $opened['ino'] !== $named['ino']
                || ! flock($handle, LOCK_EX)) {
                throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
            }
            fwrite(STDOUT, "P00_PG_BOOTSTRAP_BARRIER READY\n");
            fflush(STDOUT);
            if (trim((string) fgets(STDIN)) !== 'GO') {
                throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
            }
            $namedAfter = @lstat($substitutePath);
            $openedAfter = fstat($handle);
            if (! is_array($namedAfter) || ! is_array($openedAfter)
                || $openedAfter['dev'] !== $opened['dev'] || $openedAfter['ino'] !== $opened['ino']
                || $namedAfter['dev'] !== $opened['dev'] || $namedAfter['ino'] !== $opened['ino']
                || ($namedAfter['mode'] & 0777) !== 0600
                || rewind($handle) !== true) {
                throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
            }
            $contents = stream_get_contents($handle);
            if (! is_string($contents) || ! @unlink($substitutePath)) {
                throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
            }
            $substituteUrl = trim($contents);
        } finally {
            if (is_resource($handle)) {
                flock($handle, LOCK_UN);
                fclose($handle);
            }
            $remaining = @lstat($substitutePath);
            if (is_array($remaining) && is_array($opened)
                && $remaining['dev'] === $opened['dev'] && $remaining['ino'] === $opened['ino']) {
                @unlink($substitutePath);
            }
        }
        PostgresConnectionProfile::fromUrl($substituteUrl);
        if (! putenv('DB_URL='.$substituteUrl)) {
            throw new RuntimeException('P00_PG_TEST_BARRIER_REFUSED');
        }
        $_ENV['DB_URL'] = $substituteUrl;
        $_SERVER['DB_URL'] = $substituteUrl;
    }
    if (getenv('P00_PG_SCHEMA_READY') !== '1') {
        throw new RuntimeException('P00_PG_SCHEMA_READY_REFUSED');
    }
    RefreshDatabaseState::$migrated = true;
} catch (Throwable $error) {
    $fail($error->getMessage());
}
$version = (int) $live['server_version_num'];
fwrite(STDOUT, "P00_POSTGRES_GUARD PASS database={$profile->database} server_version_num={$version}\n");
