<?php

namespace App\Support;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

final class ImpersonationToken
{
    private const MARKER = 'store.impersonation';

    /** @return list<string> */
    public static function abilitiesForStore(int $storeId): array
    {
        if ($storeId < 1) {
            throw new InvalidArgumentException('An impersonation token requires a positive store ID.');
        }

        return [self::MARKER, 'store:'.$storeId];
    }

    public static function fromRequest(Request $request): ?PersonalAccessToken
    {
        $bearer = $request->bearerToken();

        if ($bearer === null || $bearer === '') {
            return null;
        }

        if (str_contains($bearer, '|')) {
            [$id, $secret] = explode('|', $bearer, 2);

            if (! ctype_digit($id) || $secret === '') {
                return null;
            }
        }

        $model = Sanctum::$personalAccessTokenModel;
        $token = $model::findToken($bearer);

        return $token instanceof PersonalAccessToken ? $token : null;
    }

    public static function isImpersonation(PersonalAccessToken $token): bool
    {
        $abilities = is_array($token->abilities) ? $token->abilities : [];

        return in_array(self::MARKER, $abilities, true)
            || preg_match('/\Aimpersonation:admin:[1-9][0-9]*\z/', (string) $token->name) === 1;
    }

    public static function boundStoreId(PersonalAccessToken $token): ?int
    {
        $abilities = $token->abilities;

        if (! is_array($abilities) || count($abilities) !== 2) {
            return null;
        }

        if (count(array_keys($abilities, self::MARKER, true)) !== 1) {
            return null;
        }

        $bindings = array_values(array_filter(
            $abilities,
            static fn (mixed $ability): bool => is_string($ability) && str_starts_with($ability, 'store:'),
        ));

        if (count($bindings) !== 1) {
            return null;
        }

        $storeId = substr($bindings[0], strlen('store:'));

        return preg_match('/\A[1-9][0-9]*\z/', $storeId) === 1
            ? (int) $storeId
            : null;
    }
}
