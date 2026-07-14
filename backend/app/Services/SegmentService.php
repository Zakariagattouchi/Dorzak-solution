<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Models\Customer;
use App\Models\CustomerSegment;
use App\Models\Store;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Saved customer segments (premium — PlanFeature::SEGMENTS). Rules filter the
 * CRM's cached per-customer counters (total_orders, total_spent), so a segment
 * resolves with a single indexed query.
 */
class SegmentService
{
    public function __construct(private readonly PlanGate $plans) {}

    /** @param array<string, mixed> $data */
    public function create(Store $store, array $data): CustomerSegment
    {
        $this->plans->ensure($store, PlanFeature::SEGMENTS);

        return CustomerSegment::create([
            'store_id' => $store->id,
            'name' => $data['name'],
            'rules' => $this->normalizeRules($data['rules'] ?? []),
        ]);
    }

    public function count(CustomerSegment $segment): int
    {
        return $this->query($segment)->count();
    }

    /** @return Collection<int, Customer> */
    public function members(CustomerSegment $segment): Collection
    {
        return $this->query($segment)->get();
    }

    private function query(CustomerSegment $segment): HasMany
    {
        $rules = $segment->rules ?? [];
        $q = $segment->store->customers();

        if (isset($rules['min_orders'])) {
            $q->where('total_orders', '>=', (int) $rules['min_orders']);
        }
        if (isset($rules['max_orders'])) {
            $q->where('total_orders', '<=', (int) $rules['max_orders']);
        }
        if (isset($rules['min_spent'])) {
            $q->where('total_spent', '>=', (float) $rules['min_spent']);
        }
        if (isset($rules['max_spent'])) {
            $q->where('total_spent', '<=', (float) $rules['max_spent']);
        }

        return $q;
    }

    /**
     * Keep only recognized numeric rules, dropping blanks so an empty field
     * never becomes a 0 threshold.
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, float|int>
     */
    private function normalizeRules(array $rules): array
    {
        $clean = [];
        foreach (['min_orders', 'max_orders'] as $k) {
            if (isset($rules[$k]) && $rules[$k] !== '' && $rules[$k] !== null) {
                $clean[$k] = (int) $rules[$k];
            }
        }
        foreach (['min_spent', 'max_spent'] as $k) {
            if (isset($rules[$k]) && $rules[$k] !== '' && $rules[$k] !== null) {
                $clean[$k] = (float) $rules[$k];
            }
        }

        return $clean;
    }
}
