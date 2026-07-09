<?php

namespace App\Support;

use App\Enums\StaffRole;
use App\Models\Store;
use App\Models\StoreUser;

/**
 * Request-scoped holder for the authenticated user's current store and role.
 *
 * Bound as a container singleton and populated by the SetStoreContext middleware.
 * The BelongsToStore trait reads store() to scope every tenant query, and the
 * ability gates read role() to authorize. See docs 06 §2.
 */
final class StoreContext
{
    private ?StoreUser $membership = null;

    /** Standalone store for the anonymous public storefront flow (no membership). */
    private ?Store $store = null;

    public function setMembership(?StoreUser $membership): void
    {
        $this->membership = $membership;
    }

    public function membership(): ?StoreUser
    {
        return $this->membership;
    }

    /** Scope queries to a store without an authenticated membership (public flow). */
    public function setStore(?Store $store): void
    {
        $this->store = $store;
    }

    public function store(): ?Store
    {
        return $this->membership?->store ?? $this->store;
    }

    public function storeId(): ?int
    {
        return $this->membership?->store_id ?? $this->store?->id;
    }

    public function role(): ?StaffRole
    {
        return $this->membership?->role;
    }

    public function hasStore(): bool
    {
        return $this->membership !== null;
    }

    /** @return list<string> */
    public function abilities(): array
    {
        return RoleMatrix::abilitiesFor($this->role());
    }
}
