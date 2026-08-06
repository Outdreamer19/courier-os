<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

/**
 * Creates (or repoints) a staff account inside a tenant.
 *
 * Handing a pilot client their first login previously meant a fragile
 * `php artisan tinker --execute="..."` one-liner from the runbook, which is
 * easy to get subtly wrong: forget `withoutGlobalScope('tenant')` and you
 * silently edit the wrong tenant's user, forget `email_verified_at` and the
 * client is stuck on the verification screen forever.
 *
 * Examples:
 *   php artisan tenant:user today admin@todayshippingja.com \
 *       --name="Today Shipping Admin" --role=owner
 *
 *   php artisan tenant:user today ops@todayshippingja.com \
 *       --role=staff --password='...' --no-interaction
 */
class ProvisionTenantUser extends Command
{
    protected $signature = 'tenant:user
                            {subdomain : The tenant subdomain, e.g. "today"}
                            {email : The account email address}
                            {--name= : Display name (defaults to the email local-part)}
                            {--role=owner : owner, admin, staff or customer}
                            {--password= : Password to set (a strong one is generated when omitted)}
                            {--unverified : Leave the email unverified (they must click the verification link)}';

    protected $description = 'Create or update a staff/owner account inside a tenant, ready to sign in.';

    public function handle(TenantManager $tenants): int
    {
        $subdomain = (string) $this->argument('subdomain');
        $email = strtolower(trim((string) $this->argument('email')));
        $role = (string) $this->option('role');

        $tenant = Tenant::query()->where('subdomain', $subdomain)->first();

        if (! $tenant) {
            $this->components->error("No tenant found with subdomain [{$subdomain}].");

            return self::FAILURE;
        }

        $allowedRoles = [...User::adminRoles(), User::ROLE_CUSTOMER];

        if (! in_array($role, $allowedRoles, true)) {
            $this->components->error(
                "Invalid role [{$role}]. Use one of: ".implode(', ', $allowedRoles).'.'
            );

            return self::FAILURE;
        }

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email:rfc'],
        ]);

        if ($validator->fails()) {
            $this->components->error('That does not look like a valid email address.');

            return self::FAILURE;
        }

        // A generated password is 24 random characters — long enough that it
        // never needs to satisfy a complexity rule, and it is printed once so
        // it can be handed over out-of-band.
        $generated = $this->option('password') === null;
        $password = $generated ? Str::password(24) : (string) $this->option('password');

        if (! $generated && ! $this->passwordIsAcceptable($password)) {
            return self::FAILURE;
        }

        // An email address must be unique platform-wide, so refuse to hijack
        // an account that already belongs to a different tenant.
        $existingElsewhere = User::query()
            ->withoutGlobalScope('tenant')
            ->where('email', $email)
            ->where(fn ($query) => $query
                ->where('tenant_id', '!=', $tenant->id)
                ->orWhereNull('tenant_id'))
            ->first();

        if ($existingElsewhere) {
            $this->components->error(
                "[{$email}] already belongs to another account on this platform. Use a different address."
            );

            return self::FAILURE;
        }

        $tenants->forget();
        $tenants->set($tenant);

        try {
            $user = User::query()->where('email', $email)->first();
            $created = $user === null;

            $attributes = [
                'name' => $this->option('name')
                    ?: ($user?->name ?: Str::headline(Str::before($email, '@'))),
                'password' => Hash::make($password),
                'role' => $role,
                'status' => User::STATUS_ACTIVE,
                'tenant_id' => $tenant->id,
            ];

            if (! $this->option('unverified')) {
                $attributes['email_verified_at'] = $user?->email_verified_at ?? now();
            }

            if ($created) {
                $user = new User;
                $user->email = $email;
            }

            $user->forceFill($attributes)->save();
        } finally {
            $tenants->forget();
        }

        $this->newLine();
        $this->components->info($created ? 'Account created.' : 'Account updated.');
        $this->components->twoColumnDetail('Tenant', "{$tenant->name} ({$tenant->subdomain})");
        $this->components->twoColumnDetail('Sign in at', $this->signInUrl($tenant));
        $this->components->twoColumnDetail('Email', $user->email);
        $this->components->twoColumnDetail('Role', $user->role);
        $this->components->twoColumnDetail(
            'Email verified',
            $user->email_verified_at ? 'yes' : 'no — they must click the verification link'
        );
        $this->components->twoColumnDetail('Password', $password);
        $this->newLine();
        $this->components->warn('Share the password out-of-band. It is not stored anywhere and cannot be shown again.');

        return self::SUCCESS;
    }

    private function passwordIsAcceptable(string $password): bool
    {
        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', Password::default()]],
        );

        if ($validator->fails()) {
            $this->components->error('Password rejected: '.$validator->errors()->first('password'));

            return false;
        }

        return true;
    }

    private function signInUrl(Tenant $tenant): string
    {
        $host = $tenant->custom_domain
            ?: $tenant->subdomain.'.'.config('courieros.central_domain');

        return 'https://'.$host.'/login';
    }
}
