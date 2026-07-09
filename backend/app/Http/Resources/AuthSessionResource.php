<?php

namespace App\Http\Resources;

use App\Models\StoreUser;
use App\Support\RoleMatrix;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The authenticated-session payload returned by login / register / me.
 * Shape mirrors docs 05 (auth) so the SPA can hydrate authStore + gate its nav.
 *
 * Wraps a StoreUser membership; an optional plain-text token is attached for
 * bearer-token clients (issued only when the client sends device_name).
 *
 * @property StoreUser $resource
 */
class AuthSessionResource extends JsonResource
{
    public function __construct($membership, private readonly ?string $token = null)
    {
        parent::__construct($membership);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $membership = $this->resource;
        $store = $membership->store;

        return [
            'token' => $this->token,
            'user' => new UserResource($membership->user),
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'currency' => $store->currency,
                'symbol_placement' => $store->symbol_placement,
                'language' => $store->language,
                'country' => $store->country,
            ],
            'role' => $membership->role->value,
            'abilities' => RoleMatrix::abilitiesFor($membership->role),
        ];
    }
}
