<?php

namespace App\Http\Controllers\Api\Platform;

use App\Http\Controllers\Controller;
use App\Models\DeliveryProvider;
use App\Models\PlatformAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Super-admin CRUD for platform delivery providers. Fees are plain numbers in
 * the store's currency (single-market launch). Creating the FIRST active
 * provider switches every store from its flat delivery fee to calculated
 * quotes. Public store-card `delivery_mode` is cached (~60s TTL) — provider
 * edits may take up to that long to reflect on storefronts.
 */
class DeliveryProviderController extends Controller
{
    public function index(): JsonResponse
    {
        $providers = DeliveryProvider::query()->orderBy('sort_order')->orderBy('id')->get();

        return response()->json(['data' => $providers->map(fn (DeliveryProvider $p) => $this->providerShape($p))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());
        $this->guardSingleNetworkCarrier($data['kind'] ?? DeliveryProvider::KIND_COMPARATOR);

        $provider = DeliveryProvider::create($data);
        $this->audit($request, 'delivery_provider.created', $provider);

        return response()->json(['data' => $this->providerShape($provider)], 201);
    }

    public function update(Request $request, DeliveryProvider $provider): JsonResponse
    {
        $data = $request->validate($this->rules(sometimes: true, ignoreId: $provider->id));

        if (($data['kind'] ?? $provider->kind) === DeliveryProvider::KIND_NETWORK) {
            $this->guardSingleNetworkCarrier(DeliveryProvider::KIND_NETWORK, $provider->id);
        }

        $provider->update($data);
        $this->audit($request, 'delivery_provider.updated', $provider, ['changes' => array_keys($data)]);

        return response()->json(['data' => $this->providerShape($provider->fresh())]);
    }

    public function destroy(Request $request, DeliveryProvider $provider): JsonResponse
    {
        $label = $provider->name;
        $provider->delete(); // orders keep their name snapshot; FK nulls out

        PlatformAuditLog::record($request->user(), 'delivery_provider.deleted', null, $label, ['provider_id' => $provider->id], $request->ip());

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function rules(bool $sometimes = false, ?int $ignoreId = null): array
    {
        $req = $sometimes ? 'sometimes' : 'required';

        return [
            'name' => [$req, 'string', 'max:80'],
            // The carrier's identity in the delivery network's vocabulary. Only
            // codes it knows can be sent as comparator quotes (see config/delivery).
            'code' => ['nullable', 'string', 'max:20', 'alpha_dash', Rule::unique('delivery_providers', 'code')->ignore($ignoreId)],
            'kind' => ['sometimes', Rule::in([DeliveryProvider::KIND_NETWORK, DeliveryProvider::KIND_COMPARATOR])],
            // Network carriers are priced live by the delivery system, so these
            // local formula fields are ignored for them (kept for the UI's sake).
            'base_fee' => [$req, 'numeric', 'min:0'],
            'per_km_fee' => [$req, 'numeric', 'min:0'],
            'min_fee' => [$req, 'numeric', 'min:0'],
            'max_radius_km' => [$req, 'numeric', 'min:0.1', 'max:500'],
            'is_plan_gated' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ];
    }

    /**
     * Exactly one network carrier may exist: a second one would have no quote
     * source, since the network prices a trip once against all comparators.
     */
    private function guardSingleNetworkCarrier(string $kind, ?int $ignoreId = null): void
    {
        if ($kind !== DeliveryProvider::KIND_NETWORK) {
            return;
        }

        $exists = DeliveryProvider::query()->network()
            ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'kind' => ['A network carrier already exists. Only one carrier can be priced by the Dorzak delivery network.'],
            ]);
        }
    }

    private function audit(Request $request, string $action, DeliveryProvider $provider, array $meta = []): void
    {
        PlatformAuditLog::record($request->user(), $action, $provider, $provider->name, $meta, $request->ip());
    }

    private function providerShape(DeliveryProvider $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'kind' => $p->kind,
            'base_fee' => (float) $p->base_fee,
            'per_km_fee' => (float) $p->per_km_fee,
            'min_fee' => (float) $p->min_fee,
            'max_radius_km' => (float) $p->max_radius_km,
            'is_plan_gated' => $p->is_plan_gated,
            'is_active' => $p->is_active,
            'sort_order' => $p->sort_order,
        ];
    }
}
