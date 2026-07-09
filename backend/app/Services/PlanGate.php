<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Exceptions\PlanUpgradeRequiredException;
use App\Models\Store;

/**
 * The single place that answers "does this store's plan allow X?" (doc 13 §3).
 *
 * Resolves store -> current plan -> granted capabilities and memoizes the map
 * per store for the life of the request (registered as a container singleton),
 * so repeated checks in one request hit the DB once.
 *
 *  - boolean feature: allows() is true iff the plan grants it.
 *  - limit feature:   allows() is always true (everyone can have staff/products);
 *                     limit() returns the cap (null = unlimited) and
 *                     ensureWithinLimit() enforces it.
 */
class PlanGate
{
    /** @var array<int, array<string, array{present:bool, limit:int|null}>> keyed by store id */
    private array $cache = [];

    public function allows(Store $store, PlanFeature $feature): bool
    {
        if ($feature->isLimit()) {
            return true;
        }

        return $this->map($store)[$feature->value]['present'] ?? false;
    }

    /** The cap for a limit-kind feature: null = unlimited (or feature absent). */
    public function limit(Store $store, PlanFeature $feature): ?int
    {
        return $this->map($store)[$feature->value]['limit'] ?? null;
    }

    /** @throws PlanUpgradeRequiredException when a boolean capability is not granted. */
    public function ensure(Store $store, PlanFeature $feature): void
    {
        if (! $this->allows($store, $feature)) {
            throw new PlanUpgradeRequiredException($feature);
        }
    }

    /**
     * @param  int  $current  the count that already exists (members, products, ...)
     *
     * @throws PlanUpgradeRequiredException when adding one more would exceed the cap.
     */
    public function ensureWithinLimit(Store $store, PlanFeature $feature, int $current): void
    {
        $limit = $this->limit($store, $feature);

        if ($limit !== null && $current >= $limit) {
            throw new PlanUpgradeRequiredException(
                $feature,
                'You have reached your plan limit. Upgrade to add more.',
            );
        }
    }

    /** Drop the memoized map for a store after its plan changes mid-request. */
    public function forget(Store $store): void
    {
        unset($this->cache[$store->id]);
    }

    /** @return array<string, array{present:bool, limit:int|null}> */
    private function map(Store $store): array
    {
        return $this->cache[$store->id] ??= $this->build($store);
    }

    /** @return array<string, array{present:bool, limit:int|null}> */
    private function build(Store $store): array
    {
        $plan = $store->currentPlan();

        if ($plan === null) {
            return [];
        }

        $map = [];
        foreach ($plan->featureLimits as $row) {
            $map[$row->feature->value] = ['present' => true, 'limit' => $row->limit_value];
        }

        return $map;
    }
}
