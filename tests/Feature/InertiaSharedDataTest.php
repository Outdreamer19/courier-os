<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Support\Tenancy\TenantManager;
use Database\Seeders\ShippingRateSeeder;
use Database\Seeders\WarehouseAddressSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class InertiaSharedDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'localhost']);
        $tenant = Tenant::factory()->create(['subdomain' => 'shipd']);
        app(TenantManager::class)->set($tenant);
    }

    public function test_home_page_receives_active_rate_snapshot(): void
    {
        $this->seed([WarehouseAddressSeeder::class, ShippingRateSeeder::class]);

        $this->get('http://shipd.localhost/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/Home')
                ->where('rate.currency', config('shipdjm.currency'))
                ->where(
                    'rate.rate_per_lb',
                    fn ($value) => (float) $value === (float) config('shipdjm.default_rate_per_lb'),
                )
            );
    }

    public function test_warehouse_is_shared_to_inertia_pages(): void
    {
        $this->seed([WarehouseAddressSeeder::class]);

        $this->get('http://shipd.localhost/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('warehouse.city', 'Miami')
                ->where('warehouse.state', 'FL')
            );
    }
}
