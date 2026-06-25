<?php

namespace Tests\Feature\Admin;

use App\Enums\PaymentStatus;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ReportService;
use App\Support\Tenancy\TenantManager;
use Database\Seeders\CourierOsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'localhost']);
        $this->seed(CourierOsSeeder::class);
    }

    private function setTenant(string $subdomain): Tenant
    {
        $tenant = Tenant::query()->where('subdomain', $subdomain)->firstOrFail();
        app(TenantManager::class)->set($tenant);

        return $tenant;
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function test_reports_page_requires_admin_role(): void
    {
        $tenant = $this->setTenant('island');
        $customer = User::query()->where('email', 'customer@island.test')->first();

        $this->actingAs($customer)
            ->get('http://island.localhost/admin/reports')
            ->assertForbidden();
    }

    public function test_owner_can_view_reports_page(): void
    {
        $tenant = $this->setTenant('island');
        $owner = User::query()->where('email', 'owner@island.test')->first();

        $this->actingAs($owner)
            ->get('http://island.localhost/admin/reports')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('admin/reports/Index')
                ->has('revenueByMonth')
                ->has('packageVolumeByMonth')
                ->has('unpaidTotal')
                ->has('unpaidCount')
                ->has('topCustomers')
                ->where('currency', 'USD'),
            );
    }

    public function test_jmd_tenant_gets_jmd_currency_on_reports(): void
    {
        $this->setTenant('shipd');
        $owner = User::query()->where('email', 'owner@shipd.test')->first();

        $this->actingAs($owner)
            ->get('http://shipd.localhost/admin/reports')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('currency', 'JMD'));
    }

    // -------------------------------------------------------------------------
    // CSV export
    // -------------------------------------------------------------------------

    public function test_export_returns_csv_download(): void
    {
        $this->setTenant('island');
        $owner = User::query()->where('email', 'owner@island.test')->first();

        $response = $this->actingAs($owner)
            ->get('http://island.localhost/admin/reports/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Month,Revenue (USD),Packages', $response->getContent());
    }

    // -------------------------------------------------------------------------
    // ReportService — tenant scoping
    // -------------------------------------------------------------------------

    public function test_report_service_scopes_revenue_to_current_tenant(): void
    {
        $islandTenant = $this->setTenant('island');
        $islandOwner = User::query()->where('email', 'owner@island.test')->first();

        // Create a paid package for island tenant
        $islandPackage = Package::factory()->create([
            'user_id' => $islandOwner->id,
            'payment_status' => PaymentStatus::Paid,
            'amount_due' => 500.00,
            'paid_at' => now(),
        ]);

        // Create a paid package for shipd tenant
        $shipdTenant = Tenant::query()->where('subdomain', 'shipd')->first();
        app(TenantManager::class)->set($shipdTenant);
        $shipdOwner = User::query()->where('email', 'owner@shipd.test')->first();
        Package::factory()->create([
            'user_id' => $shipdOwner->id,
            'payment_status' => PaymentStatus::Paid,
            'amount_due' => 9999.00,
            'paid_at' => now(),
        ]);

        // Switch back to island and check revenue excludes shipd
        app(TenantManager::class)->set($islandTenant);
        $service = app(ReportService::class);
        $revenue = $service->revenueByMonth();

        $totalRevenue = array_sum($revenue['data']);
        $this->assertEquals(500.0, $totalRevenue);
    }

    public function test_report_service_returns_12_monthly_labels(): void
    {
        $this->setTenant('island');
        $service = app(ReportService::class);

        $revenue = $service->revenueByMonth();
        $this->assertCount(12, $revenue['labels']);
        $this->assertCount(12, $revenue['data']);

        $volume = $service->packageVolumeByMonth();
        $this->assertCount(12, $volume['labels']);
    }

    public function test_report_service_unpaid_total_is_tenant_scoped(): void
    {
        $islandTenant = $this->setTenant('island');
        $islandOwner = User::query()->where('email', 'owner@island.test')->first();

        Package::factory()->create([
            'user_id' => $islandOwner->id,
            'payment_status' => PaymentStatus::Unpaid,
            'amount_due' => 250.00,
        ]);

        // Shipd unpaid should not appear
        $shipdTenant = Tenant::query()->where('subdomain', 'shipd')->first();
        app(TenantManager::class)->set($shipdTenant);
        $shipdOwner = User::query()->where('email', 'owner@shipd.test')->first();
        Package::factory()->create([
            'user_id' => $shipdOwner->id,
            'payment_status' => PaymentStatus::Unpaid,
            'amount_due' => 5000.00,
        ]);

        app(TenantManager::class)->set($islandTenant);
        $service = app(ReportService::class);

        $this->assertEquals(250.0, $service->unpaidTotal());
        $this->assertEquals(1, $service->unpaidCount());
    }

    public function test_top_customers_returns_correct_order(): void
    {
        $tenant = $this->setTenant('island');
        $owner = User::query()->where('email', 'owner@island.test')->first();

        $bigSpender = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $smallSpender = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Package::factory()->count(3)->create([
            'user_id' => $bigSpender->id,
            'payment_status' => PaymentStatus::Paid,
            'amount_due' => 1000.00,
            'paid_at' => now(),
        ]);
        Package::factory()->create([
            'user_id' => $smallSpender->id,
            'payment_status' => PaymentStatus::Paid,
            'amount_due' => 100.00,
            'paid_at' => now(),
        ]);

        $service = app(ReportService::class);
        $top = $service->topCustomers();

        $this->assertEquals($bigSpender->id, $top[0]['customer_id']);
        $this->assertEquals(3000.0, $top[0]['total_paid']);
        $this->assertEquals(3, $top[0]['package_count']);
    }
}
