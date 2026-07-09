<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Session payload for a platform (super) admin — a user with NO store membership.
 * Mirrors AuthSessionResource's shape but with a null store and the sentinel
 * role PLATFORM_ADMIN, so the SPA routes this session to the platform console
 * instead of the merchant back office.
 *
 * @property User $resource
 */
class PlatformSessionResource extends JsonResource
{
    public function __construct($user, private readonly ?string $token = null)
    {
        parent::__construct($user);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'user' => new UserResource($this->resource),
            'store' => null,
            'role' => 'PLATFORM_ADMIN',
            'abilities' => [],
        ];
    }
}
