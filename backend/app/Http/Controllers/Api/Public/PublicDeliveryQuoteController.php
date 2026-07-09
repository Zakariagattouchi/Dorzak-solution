<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Concerns\ResolvesPublicStore;
use App\Http\Controllers\Controller;
use App\Services\DeliveryQuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/public/stores/{slug}/delivery-quote?lat&lng&subtotal
 *
 * Prices a delivery for the checkout preview BEFORE the order is placed. The
 * same DeliveryQuoteService re-runs at placement, so this response is purely
 * informational — never trusted as money. Exposes no store coordinates and no
 * provider pricing internals, only the resulting fee. Never cached.
 */
class PublicDeliveryQuoteController extends Controller
{
    use ResolvesPublicStore;

    public function __invoke(Request $request, string $slug, DeliveryQuoteService $quotes): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $store = $this->resolvePublicStore($slug);

        $quote = $quotes->quote($store, (float) $data['lat'], (float) $data['lng'], (float) $data['subtotal']);

        return response()->json([
            'data' => [
                'available' => $quote['mode'] !== 'unavailable',
                'mode' => $quote['mode'],
                'fee' => $quote['fee'],
                'distance_km' => $quote['distance_km'],
                'provider_name' => $quote['provider_name'],
                'waived' => $quote['waived'],
                'reason' => $quote['reason'],
            ],
        ], 200, ['Cache-Control' => 'no-store']);
    }
}
