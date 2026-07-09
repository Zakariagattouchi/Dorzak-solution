<?php

namespace App\Support;

/**
 * Geographic helpers for delivery pricing. Straight-line (haversine) distance
 * is deliberate for v1 — no routing API; providers price base + per-km over
 * the crow-flies distance and cap availability with max_radius_km.
 */
final class Geo
{
    private const EARTH_RADIUS_KM = 6371.0088;

    /** Great-circle distance between two coordinates, in km (2 decimals). */
    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        return round(self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }
}
