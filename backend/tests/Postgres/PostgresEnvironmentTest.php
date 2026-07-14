<?php

namespace Tests\Postgres;

use App\Support\PostgresQualificationGuard;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PostgresEnvironmentTest extends TestCase
{
    public function test_lane_is_postgresql_16(): void
    {
        self::assertTrue(
            getenv('P00_E2E_SUPERVISOR_DB_URL') === false,
            'Qualified children must not inherit the supervisor credential.',
        );
        self::assertSame('pgsql', DB::connection()->getDriverName());
        $profile = PostgresQualificationGuard::assertBootstrapAuthority(
            (string) getenv('DB_URL'),
            (string) getenv('P00_PG_IDENTITY'),
            (string) getenv('P00_PG_ATTESTATION_SHA256'),
            (string) getenv('P00_PG_INSTANCE_NONCE_SHA256'),
            (string) getenv('P00_PG_QUALIFICATION_NONCE_SHA256'),
        );
        $identity = PostgresQualificationGuard::assertPdo(
            DB::connection()->getPdo(),
            $profile,
            (string) getenv('P00_PG_INSTANCE_NONCE_SHA256'),
        );
        PostgresQualificationGuard::assertActivated(
            DB::connection()->getPdo(),
            (string) getenv('P00_PG_QUALIFICATION_NONCE_SHA256'),
        );
        PostgresQualificationGuard::assertBootstrapLive($identity);
        $version = (int) $identity['server_version_num'];
        self::assertGreaterThanOrEqual(160000, $version);
        self::assertLessThan(170000, $version);
    }
}
