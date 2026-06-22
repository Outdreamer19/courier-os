<?php

namespace Tests\Feature\Tenancy;

use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Database\Seeders\CourierOsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_a_platform_owner(): void
    {
        $this->seed(CourierOsSeeder::class);

        $owner = User::query()->where('role', User::ROLE_PLATFORM_OWNER)->first();

        $this->assertNotNull($owner);
        $this->assertNull($owner->tenant_id);
    }

    public function test_seeder_creates_multiple_tenants_with_scoped_data(): void
    {
        $this->seed(CourierOsSeeder::class);

        $tenants = Tenant::query()->get();
        $this->assertGreaterThanOrEqual(2, $tenants->count());

        foreach ($tenants as $tenant) {
            app(TenantManager::class)->set($tenant);

            // Each tenant has at least an owner user and some data.
            $this->assertGreaterThanOrEqual(1, User::query()->where('role', User::ROLE_OWNER)->count());
            $this->assertGreaterThanOrEqual(1, CustomerProfile::query()->count());

            // All packages for this tenant carry its tenant_id.
            Package::query()->get()->each(function (Package $package) use ($tenant) {
                $this->assertSame($tenant->id, $package->tenant_id);
            });
        }
    }

    public function test_seeded_tenants_have_unique_reference_prefixes(): void
    {
        $this->seed(CourierOsSeeder::class);

        $prefixes = Tenant::query()->pluck('customer_reference_prefix');

        $this->assertSame($prefixes->count(), $prefixes->unique()->count());
    }
}
