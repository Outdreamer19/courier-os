<?php

namespace Tests\Feature\Tenancy;

use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The CourierOS platform domain resolves no tenant, which switches off the
 * BelongsToTenant global scope, EnsureUserBelongsToTenant and
 * EnsureTenantSubscribed all at once — every one of them by design, so that
 * platform code can query across tenants.
 *
 * Routes in routes/web.php carry no domain constraint, so before the `tenant`
 * middleware existed those same routes answered on courieros.co with all
 * three protections disabled. These tests pin that boundary shut, and pin
 * open the unrecognised-host path that single-courier installs rely on.
 */
class CentralDomainBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['courieros.central_domain' => 'courieros.co']);
    }

    private function tenantUser(Tenant $tenant, string $role): User
    {
        app(TenantManager::class)->set($tenant);

        $user = User::factory()->create([
            'role' => $role,
            'tenant_id' => $tenant->id,
        ]);

        app(TenantManager::class)->forget();

        return $user;
    }

    private function platformOwner(): User
    {
        app(TenantManager::class)->forget();

        return User::factory()->create([
            'role' => User::ROLE_PLATFORM_OWNER,
            'tenant_id' => null,
        ]);
    }

    public function test_tenant_admin_cannot_open_the_admin_console_on_the_platform_domain(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $admin = $this->tenantUser($tenant, User::ROLE_OWNER);

        $response = $this->actingAs($admin)->get('http://courieros.co/admin');

        $this->assertNotSame(200, $response->getStatusCode());
    }

    public function test_tenant_admin_can_open_the_admin_console_on_their_own_host(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $admin = $this->tenantUser($tenant, User::ROLE_OWNER);

        $this->actingAs($admin)
            ->get('http://acme.courieros.co/admin')
            ->assertOk();
    }

    public function test_admin_console_does_not_report_another_tenants_packages(): void
    {
        $acme = Tenant::factory()->create(['subdomain' => 'acme']);
        $rival = Tenant::factory()->create(['subdomain' => 'rival']);

        $admin = $this->tenantUser($acme, User::ROLE_OWNER);
        $rivalCustomer = $this->tenantUser($rival, User::ROLE_CUSTOMER);

        app(TenantManager::class)->set($rival);
        Package::factory()->count(3)->create(['user_id' => $rivalCustomer->id]);
        app(TenantManager::class)->forget();

        $this->actingAs($admin)
            ->get('http://acme.courieros.co/admin')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('stats.total_packages', 0));
    }

    public function test_customer_portal_is_unreachable_on_the_platform_domain(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $customer = $this->tenantUser($tenant, User::ROLE_CUSTOMER);

        $response = $this->actingAs($customer)->get('http://courieros.co/portal/packages');

        $this->assertNotSame(200, $response->getStatusCode());
    }

    public function test_tenant_user_on_the_platform_domain_is_sent_to_their_own_site(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $customer = $this->tenantUser($tenant, User::ROLE_CUSTOMER);

        $this->actingAs($customer)
            ->get('http://courieros.co/dashboard')
            ->assertRedirect('https://acme.courieros.co/login');

        $this->assertGuest();
    }

    public function test_a_white_label_tenant_is_sent_to_their_custom_domain(): void
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => 'acme',
            'custom_domain' => 'todayshippingja.com',
        ]);
        $customer = $this->tenantUser($tenant, User::ROLE_CUSTOMER);

        $this->actingAs($customer)
            ->get('http://courieros.co/dashboard')
            ->assertRedirect('https://todayshippingja.com/login');
    }

    public function test_reserved_subdomains_are_treated_as_the_platform_domain(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $admin = $this->tenantUser($tenant, User::ROLE_OWNER);

        $response = $this->actingAs($admin)->get('http://app.courieros.co/admin');

        $this->assertNotSame(200, $response->getStatusCode());
    }

    public function test_suspended_tenant_cannot_bypass_the_subscription_guard_centrally(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $admin = $this->tenantUser($tenant, User::ROLE_OWNER);

        // Suspension is enforced by ResolveTenant on the tenant's own host, so
        // the platform domain was the only way in: no tenant resolves there,
        // and EnsureTenantSubscribed skips itself when there is no tenant.
        $tenant->update(['status' => Tenant::STATUS_SUSPENDED]);

        $response = $this->actingAs($admin)->get('http://courieros.co/admin');

        $this->assertNotSame(200, $response->getStatusCode());
    }

    public function test_platform_owner_still_reaches_the_platform_console(): void
    {
        $this->actingAs($this->platformOwner())
            ->get('http://courieros.co/dashboard')
            ->assertRedirect(route('central.platform.dashboard'));
    }

    public function test_platform_owner_is_still_kept_out_of_tenant_hosts(): void
    {
        Tenant::factory()->create(['subdomain' => 'acme']);

        $this->actingAs($this->platformOwner())
            ->get('http://acme.courieros.co/dashboard')
            ->assertForbidden();
    }

    /**
     * An unrecognised host is not the platform domain, so a single-courier
     * install (and the rest of this test suite, which runs on localhost)
     * keeps working with no tenant bound at all.
     */
    public function test_an_unrecognised_host_is_not_treated_as_the_platform_domain(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_OWNER]);

        $this->actingAs($admin)
            ->get('http://localhost/admin')
            ->assertOk();
    }
}
