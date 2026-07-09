<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Manage platform (super) admins — a distinct identity that is NOT a merchant:
 * a user with is_platform_admin and NO store membership. This is the bootstrap
 * path (the in-app "grant admin" action itself requires an existing admin).
 *
 *   platform:admin admin@dorzak.com --create --password=secret   # mint a new one
 *   platform:admin someone@shop.com                              # promote existing
 *   platform:admin someone@shop.com --revoke                     # demote
 */
class GrantPlatformAdmin extends Command
{
    protected $signature = 'platform:admin {email}
        {--revoke : Remove platform-admin access instead of granting it}
        {--create : Create a brand-new store-less admin user if none exists}
        {--name=Platform Admin : Name for a newly created admin}
        {--password= : Password for a newly created admin}';

    protected $description = 'Grant, revoke, or create a platform (super) admin';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if ($user === null && $this->option('create')) {
            $password = $this->option('password') ?: $this->secret('Password for the new admin');

            if (! $password) {
                $this->error('A --password is required to create an admin.');

                return self::FAILURE;
            }

            $user = User::create([
                'name' => $this->option('name'),
                'email' => $email,
                'password' => Hash::make($password),
                'is_platform_admin' => true,
            ]);

            $this->info("Created store-less platform admin {$user->email}.");

            return self::SUCCESS;
        }

        if ($user === null) {
            $this->error("No user found with email {$email}. Pass --create to make one.");

            return self::FAILURE;
        }

        $grant = ! $this->option('revoke');
        $user->forceFill(['is_platform_admin' => $grant])->save();

        $this->info($grant
            ? "{$user->email} is now a platform admin."
            : "{$user->email} is no longer a platform admin.");

        return self::SUCCESS;
    }
}
