<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Support\Facades\DB;

/**
 * Provisions a new tenant (pending) together with its owner user.
 *
 * The tenant starts in the pending state and only becomes active once
 * Stripe Checkout completes (handled by the billing webhook). Owner user
 * creation runs inside the new tenant's scope so the user is correctly
 * tagged with tenant_id.
 *
 * @phpstan-type SignupData array{
 *     business_name: string,
 *     subdomain: string,
 *     currency: string,
 *     customer_reference_prefix: string,
 *     owner_name: string,
 *     owner_email: string,
 *     password: string,
 * }
 */
class CreateTenant
{
    public function __construct(private readonly TenantManager $tenants) {}

    /**
     * @param  SignupData  $data
     */
    public function handle(array $data): Tenant
    {
        return DB::transaction(function () use ($data): Tenant {
            $tenant = Tenant::create([
                'name' => $data['business_name'],
                'subdomain' => $data['subdomain'],
                'status' => Tenant::STATUS_PENDING,
                'currency' => $data['currency'],
                'customer_reference_prefix' => strtoupper($data['customer_reference_prefix']),
                'package_reference_prefix' => 'PKG',
            ]);

            // Create the owner user within the tenant's scope so the global
            // scope auto-fills tenant_id correctly.
            $previous = $this->tenants->current();
            $this->tenants->set($tenant);

            try {
                User::create([
                    'name' => $data['owner_name'],
                    'email' => $data['owner_email'],
                    'password' => $data['password'],
                    'role' => User::ROLE_OWNER,
                    'status' => User::STATUS_ACTIVE,
                ]);
            } finally {
                if ($previous) {
                    $this->tenants->set($previous);
                } else {
                    $this->tenants->forget();
                }
            }

            return $tenant->fresh();
        });
    }
}
