<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Promotes or demotes a platform (super) admin by email. This is the bootstrap
 * path — the very first super admin can only be minted from the CLI, since the
 * in-app "grant admin" action itself requires an existing super admin. See doc 13.
 */
class GrantPlatformAdmin extends Command
{
    protected $signature = 'platform:admin {email} {--revoke : Remove platform-admin access instead of granting it}';

    protected $description = 'Grant or revoke platform (super) admin access for a user';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error("No user found with email {$this->argument('email')}.");

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
