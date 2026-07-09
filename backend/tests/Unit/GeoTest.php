<?php

namespace Tests\Unit;

use App\Support\Geo;
use PHPUnit\Framework\TestCase;

class GeoTest extends TestCase
{
    public function test_zero_distance_for_identical_points(): void
    {
        $this->assertSame(0.0, Geo::distanceKm(25.2854, 51.531, 25.2854, 51.531));
    }

    public function test_known_doha_pair(): void
    {
        // Souq Waqif (25.2867, 51.5333) -> Katara (25.3548, 51.5266): ~7.6 km.
        $this->assertEqualsWithDelta(7.6, Geo::distanceKm(25.2867, 51.5333, 25.3548, 51.5266), 0.3);
    }

    public function test_symmetry(): void
    {
        $a = Geo::distanceKm(25.28, 51.53, 25.35, 51.44);
        $b = Geo::distanceKm(25.35, 51.44, 25.28, 51.53);

        $this->assertSame($a, $b);
    }
}
