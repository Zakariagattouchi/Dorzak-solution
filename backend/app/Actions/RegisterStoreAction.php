<?php

namespace App\Actions;

use App\Enums\StaffRole;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Bootstraps a brand-new tenant: user + store + OWNER membership.
 *
 * TP-02 amends this to also seed the default 1:1 settings rows (storefront/receipt/
 * integration) and a FREE subscription. Kept as an Action so both /auth/register and
 * seeders share one path. See docs 05 (register) and 08 (TP-01/TP-02).
 */
class RegisterStoreAction
{
    /**
     * @param  array{name:string,email:string,password:string,business_name:string}  $data
     * @return array{user:User,store:Store,membership:StoreUser}
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // hashed by the model cast
            ]);

            $store = Store::create([
                'name' => $data['business_name'],
                'owner_name' => $data['name'],
                'email' => $data['email'],
            ]);

            // Reload so DB-level column defaults (currency, language, country, ...)
            // are present on the instance the session payload serializes.
            $store->refresh();

            // Seed the three 1:1 settings rows (storefront/receipt/integration).
            // TP-03 additionally seeds a FREE subscription here.
            $store->initializeSettings();

            $membership = StoreUser::create([
                'store_id' => $store->id,
                'user_id' => $user->id,
                'role' => StaffRole::OWNER,
                'is_active' => true,
                'joined_at' => now(),
            ]);

            $membership->setRelation('store', $store);
            $membership->setRelation('user', $user);

            return ['user' => $user, 'store' => $store, 'membership' => $membership];
        });
    }
}
