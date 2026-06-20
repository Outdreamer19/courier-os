<?php

namespace Tests\Feature\Tenancy;

use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function bind(Tenant $tenant): void
    {
        app(TenantManager::class)->set($tenant);
    }

    private function makeCustomerWithPackage(Tenant $tenant, string $ref): Package
    {
        $this->bind($tenant);

        $user = User::factory()->create();
        CustomerProfile::factory()->create([
            'user_id' => $user->id,
            'customer_reference' => $ref,
        ]);

        return Package::factory()->create([
            'user_id' => $user->id,
            'package_reference' => $ref.'-PKG',
        ]);
    }

    public function test_packages_are_isolated_per_tenant(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->makeCustomerWithPackage($tenantA, 'AAA-000001');
        $this->makeCustomerWithPackage($tenantB, 'BBB-000001');

        $this->bind($tenantA);
        $packages = Package::all();
        $this->assertCount(1, $packages);
        $this->assertSame('AAA-000001-PKG', $packages->first()->package_reference);

        $this->bind($tenantB);
        $packages = Package::all();
        $this->assertCount(1, $packages);
        $this->assertSame('BBB-000001-PKG', $packages->first()->package_reference);
    }

    public function test_customers_are_isolated_per_tenant(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->makeCustomerWithPackage($tenantA, 'AAA-000001');
        $this->makeCustomerWithPackage($tenantB, 'BBB-000001');

        $this->bind($tenantA);
        $this->assertCount(1, CustomerProfile::all());
        $this->assertSame('AAA-000001', CustomerProfile::first()->customer_reference);
    }

    public function test_cannot_find_another_tenants_package_by_id(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $packageB = $this->makeCustomerWithPackage($tenantB, 'BBB-000001');

        // Bound as tenant A, tenant B's package id is invisible.
        $this->bind($tenantA);
        $this->assertNull(Package::find($packageB->id));
    }

    public function test_created_records_carry_the_current_tenant_id(): void
    {
        $tenant = Tenant::factory()->create();
        $package = $this->makeCustomerWithPackage($tenant, 'TEN-000001');

        $this->assertSame($tenant->id, $package->tenant_id);
        $this->assertSame($tenant->id, $package->user->tenant_id);
    }
}
