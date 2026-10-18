<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates (or resets) the CourierOS platform owner login used on the central
 * domain (courieros.co/login, then /platform).
 *
 *   php artisan platform:owner you@example.com --name="Shane Bell"
 *
 * Prints a strong generated password once unless --password is given.
 * Running it again for the same email resets that account's password.
 */
class ProvisionPlatformOwner extends Command
{
    protected $signature = 'platform:owner
                            {email : Login email for the platform owner}
                            {--name= : Display name (defaults to the email local-part)}
                            {--password= : Password to set (a strong one is generated when omitted)}';

    protected $description = 'Create or reset the CourierOS platform owner login.';

    public function handle(TenantManager $tenants): int
    {
        $tenants->forget();

        $email = strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error("[{$email}] is not a valid email address.");

            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?: Str::password(20, symbols: false));

        $user = User::query()->whereNull('tenant_id')->where('email', $email)->first() ?? new User;

        $existed = $user->exists;

        $user->forceFill([
            'name' => $this->option('name') ?: ($user->name ?: Str::before($email, '@')),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_PLATFORM_OWNER,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'tenant_id' => null,
        ])->save();

        $this->components->info(($existed ? 'Platform owner reset: ' : 'Platform owner created: ').$email);
        $this->components->twoColumnDetail('Log in at', 'https://'.config('courieros.central_domain').'/login');

        if (! $this->option('password')) {
            $this->components->twoColumnDetail('Password (shown once)', $password);
        }

        return self::SUCCESS;
    }
}
