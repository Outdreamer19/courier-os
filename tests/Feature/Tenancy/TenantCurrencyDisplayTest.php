<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Database\Seeders\CourierOsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: tenant-facing pages must display the tenant's own currency,
 * not the legacy global config value (which was hardcoded to JMD).
 */
class TenantCurrencyDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'localhost']);
        $this->seed(CourierOsSeeder::class);
    }

    private function adminFor(string $subdomain): array
    {
        $tenant = Tenant::query()->where('subdomain', $subdomain)->first();
        app(TenantManager::class)->set($tenant);
        $owner = User::query()->where('email', "owner@{$subdomain}.test")->first();

        return [$tenant, $owner];
    }

    public function test_usd_tenant_sees_usd_on_packages_page(): void
    {
        [$tenant, $owner] = $this->adminFor('island');

        $this->actingAs($owner)
            ->get('http://island.localhost/admin/packages')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('currency', 'USD'));
    }

    public function test_jmd_tenant_sees_jmd_on_packages_page(): void
    {
        [$tenant, $owner] = $this->adminFor('shipd');

        $this->actingAs($owner)
            ->get('http://shipd.localhost/admin/packages')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('currency', 'JMD'));
    }

    public function test_usd_tenant_sees_usd_in_brand_props(): void
    {
        [$tenant, $owner] = $this->adminFor('island');

        $this->actingAs($owner)
            ->get('http://island.localhost/admin')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('brand.currency', 'USD'));
    }
}
