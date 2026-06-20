<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_tenant_can_be_created_with_core_attributes(): void
    {
        $tenant = Tenant::create([
            'name' => 'Acme Courier',
            'subdomain' => 'acme',
            'currency' => 'USD',
            'customer_reference_prefix' => 'ACM',
            'package_reference_prefix' => 'PKG',
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Acme Courier',
            'subdomain' => 'acme',
            'currency' => 'USD',
            'customer_reference_prefix' => 'ACM',
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $this->assertTrue($tenant->isActive());
    }

    public function test_subdomain_must_be_unique(): void
    {
        Tenant::create([
            'name' => 'First',
            'subdomain' => 'dup',
            'customer_reference_prefix' => 'FST',
        ]);

        $this->expectException(QueryException::class);

        Tenant::create([
            'name' => 'Second',
            'subdomain' => 'dup',
            'customer_reference_prefix' => 'SEC',
        ]);
    }

    public function test_status_defaults_to_pending(): void
    {
        $tenant = Tenant::create([
            'name' => 'Pending Co',
            'subdomain' => 'pending',
            'customer_reference_prefix' => 'PND',
        ]);

        $this->assertSame(Tenant::STATUS_PENDING, $tenant->status);
        $this->assertFalse($tenant->isActive());
    }

    public function test_currency_defaults_to_usd(): void
    {
        $tenant = Tenant::create([
            'name' => 'Default Currency',
            'subdomain' => 'defcur',
            'customer_reference_prefix' => 'DFC',
        ]);

        $this->assertSame('USD', $tenant->currency);
    }

    public function test_active_scope_only_returns_active_tenants(): void
    {
        Tenant::create(['name' => 'A', 'subdomain' => 'a', 'customer_reference_prefix' => 'A', 'status' => Tenant::STATUS_ACTIVE]);
        Tenant::create(['name' => 'B', 'subdomain' => 'b', 'customer_reference_prefix' => 'B', 'status' => Tenant::STATUS_SUSPENDED]);
        Tenant::create(['name' => 'C', 'subdomain' => 'c', 'customer_reference_prefix' => 'C', 'status' => Tenant::STATUS_PENDING]);

        $active = Tenant::query()->active()->get();

        $this->assertCount(1, $active);
        $this->assertSame('a', $active->first()->subdomain);
    }

    public function test_custom_domain_is_unique_when_set(): void
    {
        Tenant::create([
            'name' => 'Custom One',
            'subdomain' => 'custone',
            'customer_reference_prefix' => 'CO1',
            'custom_domain' => 'track.acme.com',
        ]);

        $this->expectException(QueryException::class);

        Tenant::create([
            'name' => 'Custom Two',
            'subdomain' => 'custtwo',
            'customer_reference_prefix' => 'CO2',
            'custom_domain' => 'track.acme.com',
        ]);
    }
}
