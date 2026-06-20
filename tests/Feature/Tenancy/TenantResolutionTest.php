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
