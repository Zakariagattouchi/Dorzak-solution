<?php

namespace Tests\LivePostgres;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class QualificationBootstrapProbeTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_feature_application_must_never_reach_mutation(): void
    {
        self::fail('Qualification application boot accepted the substituted endpoint.');
    }
}
