<?php

namespace Tests\Feature\Central;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'courieros.co']);
    }

    private function platformOwner(): User
    {
        // Platform owners live in central context with no tenant_id.
        app(TenantManager::class)->forget();

        return User::factory()->create([
            'role' => User::ROLE_PLATFORM_OWNER,
            'tenant_id' => null,
        ]);
    }

    public function test_platform_owner_can_view_the_tenants_dashboard(): void
    {
        Tenant::factory()->count(3)->create();
        Tenant::factory()->suspended()->create();

        $this->actingAs($this->platformOwner())
            ->get('http://courieros.co/platform')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('central/admin/Dashboard')
                ->where('stats.total_tenants', 4)
                ->where('stats.active_tenants', 3)
                ->where('stats.suspended_tenants', 1)
            );
    }

    public function test_platform_owner_can_list_all_tenants_across_scopes(): void
    {
        Tenant::factory()->create(['name' => 'Alpha Couriers']);
        Tenant::factory()->create(['name' => 'Beta Logistics']);

        $this->actingAs($this->platformOwner())
            ->get('http://courieros.co/platform/tenants')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('central/admin/Tenants')
                ->has('tenants', 2)
            );
    }

    public function test_platform_owner_can_suspend_a_tenant(): void
    {
        $tenant = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);

        $this->actingAs($this->platformOwner())
            ->patch("http://courieros.co/platform/tenants/{$tenant->id}", ['action' => 'suspend'])
            ->assertRedirect();

        $this->assertSame(Tenant::STATUS_SUSPENDED, $tenant->fresh()->status);
    }

    public function test_platform_owner_can_reactivate_a_tenant(): void
    {
        $tenant = Tenant::factory()->suspended()->create();

        $this->actingAs($this->platformOwner())
            ->patch("http://courieros.co/platform/tenants/{$tenant->id}", ['action' => 'activate'])
            ->assertRedirect();

        $this->assertSame(Tenant::STATUS_ACTIVE, $tenant->fresh()->status);
    }

    public function test_tenant_owner_cannot_access_platform_admin(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme', 'status' => Tenant::STATUS_ACTIVE]);
        app(TenantManager::class)->set($tenant);
        $tenantOwner = User::factory()->create(['role' => User::ROLE_OWNER]);

        $this->actingAs($tenantOwner)
            ->get('http://courieros.co/platform')
            ->assertForbidden();
    }

    public function test_guests_are_redirected_from_platform_admin(): void
    {
        $this->get('http://courieros.co/platform')->assertRedirect();
    }
}
