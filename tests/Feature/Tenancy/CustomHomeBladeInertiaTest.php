<?php

namespace Tests\Feature\Tenancy;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Support\Header;
use Tests\TestCase;

/**
 * The `today` tenant serves a bespoke Blade marketing page instead of the
 * shared Inertia public/Home. An Inertia XHR that lands on that Blade
 * response (e.g. logout → redirect → /) is shown inside Inertia's error
 * <dialog> modal. These tests pin the full-page location escape hatch.
 */
class CustomHomeBladeInertiaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['courieros.central_domain' => 'courieros.co']);
    }

    /**
     * @return array<string, string>
     */
    private function inertiaHeaders(): array
    {
        $version = (string) (new HandleInertiaRequests)->version(request());

        return [
            Header::INERTIA => 'true',
            Header::VERSION => $version,
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'text/html, application/xhtml+xml',
        ];
    }

    private function todayTenant(): Tenant
    {
        return Tenant::factory()->create(['subdomain' => 'today']);
    }

    private function todayUser(Tenant $tenant): User
    {
        app(TenantManager::class)->set($tenant);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        app(TenantManager::class)->forget();

        return $user;
    }

    public function test_today_home_still_renders_blade_for_a_normal_browser_visit(): void
    {
        $this->todayTenant();

        $this->get('http://today.courieros.co/')
            ->assertOk()
            ->assertSee('TODAY Shipping', false);
    }

    public function test_inertia_visit_to_today_home_forces_a_full_page_location(): void
    {
        $this->todayTenant();

        $response = $this->withHeaders($this->inertiaHeaders())
            ->get('http://today.courieros.co/');

        $response->assertStatus(409);
        $response->assertHeader(Header::LOCATION, 'http://today.courieros.co');
    }

    public function test_logout_follow_up_to_today_home_forces_a_full_page_location(): void
    {
        $tenant = $this->todayTenant();
        $user = $this->todayUser($tenant);

        $logout = $this->actingAs($user)
            ->withHeaders($this->inertiaHeaders())
            ->post('http://today.courieros.co/logout');

        $logout->assertRedirect('http://today.courieros.co');
        $this->assertGuest();

        $home = $this->withHeaders($this->inertiaHeaders())
            ->get('http://today.courieros.co/');

        $home->assertStatus(409);
        $home->assertHeader(Header::LOCATION, 'http://today.courieros.co');
    }

    public function test_generic_tenant_home_still_returns_inertia_for_inertia_visits(): void
    {
        Tenant::factory()->create(['subdomain' => 'acme']);

        $this->get('http://acme.courieros.co/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('public/Home'));
    }
}
