<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Models\DeliveryProvider;
use App\Models\Store;
use App\Support\Geo;

/**
 * The single place that prices a delivery (doc 13 addendum). Resolves one of
 * four modes for a store + customer pin:
 *
 *  - quoted           cheapest eligible provider priced the trip
 *  - flat             legacy: NO active providers exist platform-wide, so the
 *                     store's flat delivery_fee applies (pre-provider behavior)
 *  - whatsapp_pending no eligible provider but the store opted into manual
 *                     WhatsApp quotes — order goes out with fee TO BE SET
 *  - unavailable      no eligible provider and no fallback → delivery refused
 *
 * Eligibility: provider is active, (open OR the plan grants DELIVERY_SERVICES),
 * and the haversine distance is within its max_radius_km. The merchant's
 * free_delivery_threshold still waives a quoted fee (their marketing lever).
 * Amounts are plain numbers in the store's currency (no FX).
 */
class DeliveryQuoteService
{
    public function __construct(private readonly PlanGate $plans) {}

    /**
     * Price a specific trip.
     *
     * Returns EVERY eligible courier so the customer can choose at checkout,
     * cheapest first. `$chosenProviderId` selects one of them; when it is null
     * (or not eligible for this trip) the cheapest wins. The selected option is
     * flattened onto fee/provider_id/provider_name for callers that just want
     * the price.
     *
     * @return array{mode:string, fee:?float, distance_km:?float, provider_id:?int, provider_name:?string, waived:bool, reason:?string, options:list<array{id:int,name:string,fee:float,distance_km:float,is_plan_perk:bool}>}
     */
    public function quote(Store $store, float $lat, float $lng, float $subtotal, ?int $chosenProviderId = null): array
    {
        $sf = $store->storefrontSetting;

        // Legacy mode: the platform has no providers yet — flat fee semantics.
        if (! DeliveryProvider::query()->active()->exists()) {
            $fee = (float) ($sf?->delivery_fee ?? 0);
            $waived = $this->thresholdWaives($sf, $subtotal);

            return $this->result('flat', $waived ? 0.0 : $fee, null, null, null, $waived);
        }

        // No pickup point => nothing can collect the order. This is NOT eligible
        // for the WhatsApp fallback: a manually-quoted order still has to be
        // collected from somewhere, and we refuse to record an order we cannot
        // hand to a courier.
        if (! $store->hasLocation()) {
            return $this->result('unavailable', null, null, null, null, false, 'STORE_LOCATION_MISSING');
        }

        $distance = Geo::distanceKm((float) $store->latitude, (float) $store->longitude, $lat, $lng);
        $planEligible = $this->plans->allows($store, PlanFeature::DELIVERY_SERVICES);

        $candidates = DeliveryProvider::query()->active()->get()
            ->filter(fn (DeliveryProvider $p) => ! $p->is_plan_gated || $planEligible);

        $inRange = $candidates
            ->filter(fn (DeliveryProvider $p) => $distance <= (float) $p->max_radius_km)
            ->sortBy(fn (DeliveryProvider $p) => [$p->feeFor($distance), $p->sort_order, $p->id])
            ->values();

        if ($inRange->isEmpty()) {
            return $this->fallbackOr($sf, $candidates->isNotEmpty() ? 'OUT_OF_RANGE' : 'NO_PROVIDER');
        }

        // The customer's pick, when it is genuinely available for this trip.
        $chosen = $chosenProviderId !== null
            ? $inRange->firstWhere('id', $chosenProviderId)
            : null;
        $chosen ??= $inRange->first();

        $waived = $this->thresholdWaives($sf, $subtotal);

        $options = $inRange->map(fn (DeliveryProvider $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'fee' => $waived ? 0.0 : $p->feeFor($distance),
            'distance_km' => $distance,
            'is_plan_perk' => (bool) $p->is_plan_gated,
        ])->all();

        return $this->result(
            'quoted',
            $waived ? 0.0 : $chosen->feeFor($distance),
            $distance,
            $chosen->id,
            $chosen->name,
            $waived,
            null,
            $options,
        );
    }

    /**
     * Pin-independent capability for the public store card:
     * 'quoted' | 'whatsapp_pending' | 'none'.
     */
    public function storeMode(Store $store): string
    {
        $sf = $store->storefrontSetting;

        if (! ($sf?->allow_delivery ?? false)) {
            return 'none';
        }

        // Legacy flat mode still counts as priced delivery.
        if (! DeliveryProvider::query()->active()->exists()) {
            return 'quoted';
        }

        // Once a delivery system exists, a store without a pickup point cannot
        // offer delivery at all — not even via the manual WhatsApp quote.
        if (! $store->hasLocation()) {
            return 'none';
        }

        $planEligible = $this->plans->allows($store, PlanFeature::DELIVERY_SERVICES);
        $hasProvider = DeliveryProvider::query()->active()->get()
            ->contains(fn (DeliveryProvider $p) => ! $p->is_plan_gated || $planEligible);

        if ($hasProvider) {
            return 'quoted';
        }

        return $this->fallbackEnabled($sf) ? 'whatsapp_pending' : 'none';
    }

    private function fallbackOr($sf, string $reason): array
    {
        if ($this->fallbackEnabled($sf)) {
            return $this->result('whatsapp_pending', null, null, null, null, false, $reason);
        }

        return $this->result('unavailable', null, null, null, null, false, $reason);
    }

    private function fallbackEnabled($sf): bool
    {
        // Column lands with the WhatsApp-fallback slice; null-safe until then.
        return (bool) ($sf?->whatsapp_delivery_fallback ?? false);
    }

    private function thresholdWaives($sf, float $subtotal): bool
    {
        $threshold = $sf?->free_delivery_threshold ?? null;

        return $threshold !== null && $subtotal >= (float) $threshold;
    }

    private function result(string $mode, ?float $fee, ?float $distance, ?int $providerId, ?string $providerName, bool $waived, ?string $reason = null, array $options = []): array
    {
        return [
            'mode' => $mode,
            'fee' => $fee,
            'distance_km' => $distance,
            'provider_id' => $providerId,
            'provider_name' => $providerName,
            'waived' => $waived,
            'reason' => $reason,
            'options' => $options,
        ];
    }
}
