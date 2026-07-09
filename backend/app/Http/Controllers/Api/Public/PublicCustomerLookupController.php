<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Concerns\ResolvesPublicStore;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicCustomerLookupController extends Controller
{
    use ResolvesPublicStore;

    public function __invoke(Request $request, string $slug): JsonResponse
    {
        $request->validate(['phone' => 'required|string|max:30']);

        $store = $this->resolvePublicStore($slug);
        $phone = $request->string('phone');
        $normalized = preg_replace('/\D/', '', (string) $phone);

        $customer = $store->customers()
            ->where('phone_normalized', $normalized)
            ->first(['name', 'phone', 'address', 'address_details', 'city', 'latitude', 'longitude']);

        if ($customer === null) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'address_details' => $customer->address_details,
                'city' => $customer->city,
                'latitude' => $customer->latitude,
                'longitude' => $customer->longitude,
            ],
        ]);
    }
}
