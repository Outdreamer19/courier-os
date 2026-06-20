<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use App\Services\CustomerReferenceGenerator;
use App\Services\PackageReferenceGenerator;
use App\Support\Tenancy\TenantConfig;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantConfigTest extends TestCase
{
    use RefreshDatabase;

    private function bind(Tenant $tenant): void
    {
        app(TenantManager::class)->set($tenant);
    }

    public function test_customer_prefix_comes_from_current_tenant(): void
    {
        $tenant = Tenant::factory()->create(['customer_reference_prefix' => 'ACM']);
        $this->bind($tenant);

        $reference = app(CustomerReferenceGenerator::class)->next();

        $this->assertStringStartsWith('ACM-', $reference);
    }

    public function test_package_prefix_comes_from_current_tenant(): void
    {
        $tenant = Tenant::factory()->create(['package_reference_prefix' => 'BOX']);
        $this->bind($tenant);

        $reference = app(PackageReferenceGenerator::class)->next();

        $this->assertStringStartsWith('BOX-', $reference);
    }

    public function test_currency_resolves_from_tenant(): void
    {
        $tenant = Tenant::factory()->create(['currency' => 'JMD']);
        $this->bind($tenant);

        $this->assertSame('JMD', app(TenantConfig::class)->currency());
    }

    public function test_falls_back_to_config_when_no_tenant(): void
    {
        app(TenantManager::class)->forget();
        config(['courieros.currency' => 'USD']);

        $this->assertSame('USD', app(TenantConfig::class)->currency());
    }

    public function test_inertia_brand_reflects_current_tenant(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Acme Courier',
            'currency' => 'JMD',
            'subdomain' => 'acme',
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $user = null;
        $this->bind($tenant);
        $user = User::factory()->create(['role' => User::ROLE_OWNER]);

        $response = $this->actingAs($user)->get('http://acme.courieros.co/admin');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('brand.currency', 'JMD')
            ->where('brand.name', 'Acme Courier')
        );
    }
}
