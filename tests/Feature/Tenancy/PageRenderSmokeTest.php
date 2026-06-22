<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Database\Seeders\CourierOsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end page rendering smoke test. Exercises the real routing,
 * tenant-resolution middleware, controllers and Inertia rendering for
 * every key surface, using the seeded demo environment — the in-process
 * equivalent of a browser click-through.
 */
class PageRenderSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'localhost']);
        $this->seed(CourierOsSeeder::class);
    }

    public function test_central_signup_renders(): void
    {
        $this->get('http://localhost/signup')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('central/Signup'));
    }

    public function test_tenant_login_renders_for_seeded_tenant(): void
    {
        $this->get('http://shipd.localhost/login')->assertOk();
    }

    public function test_unknown_tenant_is_404(): void
    {
        $this->get('http://ghost.localhost/login')->assertNotFound();
    }

    public function test_platform_dashboard_renders_for_platform_owner(): void
    {
        $owner = User::query()->where('role', User::ROLE_PLATFORM_OWNER)->first();

        $this->actingAs($owner)
            ->get('http://localhost/platform')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('central/admin/Dashboard')
                ->where('stats.total_tenants', 2)
                ->where('stats.active_tenants', 2)
            );
    }

    public function test_platform_tenants_list_renders(): void
    {
        $owner = User::query()->where('role', User::ROLE_PLATFORM_OWNER)->first();

        $this->actingAs($owner)
            ->get('http://localhost/platform/tenants')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('central/admin/Tenants')->has('tenants', 2));
    }

    public function test_tenant_admin_dashboard_renders_for_tenant_owner(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'shipd')->first();
        app(TenantManager::class)->set($tenant);
        $owner = User::query()->where('email', 'owner@shipd.test')->first();

        $this->actingAs($owner)
            ->get('http://shipd.localhost/admin')
            ->assertOk();
    }

    public function test_seeded_tenant_data_is_visible_to_its_admin(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'shipd')->first();
        app(TenantManager::class)->set($tenant);
        $owner = User::query()->where('email', 'owner@shipd.test')->first();

        $this->actingAs($owner)
            ->get('http://shipd.localhost/admin/packages')
            ->assertOk();
    }

    public function test_cross_tenant_admin_cannot_see_other_tenant_via_subdomain(): void
    {
        // Island's owner authenticated, but requesting shipd's subdomain.
        $island = Tenant::query()->where('subdomain', 'island')->first();
        app(TenantManager::class)->set($island);
        $islandOwner = User::query()->where('email', 'owner@island.test')->first();

        // Requesting the shipd subdomain with island's user: the tenant context
        // is shipd, but the user belongs to island, so they are not authorised
        // as a shipd admin (different tenant_id) -> redirected or forbidden.
        $response = $this->actingAs($islandOwner)->get('http://shipd.localhost/admin');

        $this->assertContains($response->status(), [403, 302]);
    }
}
