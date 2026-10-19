<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['courieros.central_domain' => 'courieros.co']);

        Route::middleware(['web'])->get('/_tenant-probe', function () {
            $manager = app(TenantManager::class);

            return response()->json([
                'has_tenant' => $manager->hasTenant(),
                'tenant' => $manager->current()?->subdomain,
            ]);
        });
    }

    public function test_known_subdomain_binds_the_tenant(): void
    {
        Tenant::factory()->create(['subdomain' => 'acme', 'status' => Tenant::STATUS_ACTIVE]);

        $response = $this->get('http://acme.courieros.co/_tenant-probe');

        $response->assertOk();
        $response->assertJson(['has_tenant' => true, 'tenant' => 'acme']);
    }

    public function test_central_domain_binds_no_tenant(): void
    {
        $response = $this->get('http://courieros.co/_tenant-probe');

        $response->assertOk();
        $response->assertJson(['has_tenant' => false, 'tenant' => null]);
    }

    public function test_www_is_treated_as_central(): void
    {
        $response = $this->get('http://www.courieros.co/_tenant-probe');

        $response->assertOk();
        $response->assertJson(['has_tenant' => false]);
    }

    public function test_app_subdomain_is_treated_as_central(): void
    {
        $response = $this->get('http://app.courieros.co/_tenant-probe');

        $response->assertOk();
        $response->assertJson(['has_tenant' => false]);
    }

    public function test_unknown_subdomain_returns_404(): void
    {
        $response = $this->get('http://nope.courieros.co/_tenant-probe');

        $response->assertNotFound();
    }

    public function test_suspended_tenant_is_rejected(): void
    {
        Tenant::factory()->suspended()->create(['subdomain' => 'frozen']);

        $response = $this->get('http://frozen.courieros.co/_tenant-probe');

        $response->assertStatus(403);
    }

    public function test_unpaid_tenant_is_sent_to_finish_payment(): void
    {
        Tenant::factory()->pending()->create(['subdomain' => 'unpaid']);

        $this->get('http://unpaid.courieros.co/_tenant-probe')
            ->assertRedirect('https://courieros.co/billing/finish/unpaid');

        // Inertia XHRs (e.g. a login submit) get a full page visit instead.
        $this->withHeaders(['X-Inertia' => 'true'])
            ->get('http://unpaid.courieros.co/_tenant-probe')
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'https://courieros.co/billing/finish/unpaid');
    }

    public function test_finish_page_offers_checkout_for_an_unpaid_tenant(): void
    {
        $tenant = Tenant::factory()->pending()->create(['subdomain' => 'unpaid', 'name' => 'Unpaid Courier']);

        $this->get('http://courieros.co/billing/finish/unpaid')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('central/BillingFinish')
                ->where('tenant.name', 'Unpaid Courier')
                ->where('tenant.checkout_url', route('central.billing.checkout', ['tenant' => $tenant->id])));
    }

    public function test_finish_page_sends_a_paid_tenant_to_its_login(): void
    {
        Tenant::factory()->create(['subdomain' => 'paid']);

        $this->get('http://courieros.co/billing/finish/paid')
            ->assertRedirect('https://paid.courieros.co/login');
    }

    public function test_custom_domain_binds_the_tenant(): void
    {
        Tenant::factory()->create([
            'subdomain' => 'acme',
            'custom_domain' => 'track.acme.com',
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $response = $this->get('http://track.acme.com/_tenant-probe');

        $response->assertOk();
        $response->assertJson(['has_tenant' => true, 'tenant' => 'acme']);
    }
}
