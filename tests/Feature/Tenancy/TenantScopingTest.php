<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TenantScopedStub;
use Tests\TestCase;

class TenantScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A throwaway table to exercise the trait in isolation.
        \Schema::create('tenant_scoped_stubs', function ($table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable();
            $table->string('label');
            $table->timestamps();
        });
    }

    public function test_manager_holds_and_forgets_current_tenant(): void
    {
        $manager = app(TenantManager::class);
        $tenant = Tenant::factory()->create();

        $this->assertFalse($manager->hasTenant());

        $manager->set($tenant);

        $this->assertTrue($manager->hasTenant());
        $this->assertSame($tenant->id, $manager->current()->id);

        $manager->forget();

        $this->assertFalse($manager->hasTenant());
    }

    public function test_queries_are_scoped_to_current_tenant(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        app(TenantManager::class)->set($tenantA);
        TenantScopedStub::create(['label' => 'a-row']);

        app(TenantManager::class)->set($tenantB);
        TenantScopedStub::create(['label' => 'b-row']);

        // Bound as B: only B rows visible.
        $this->assertCount(1, TenantScopedStub::all());
        $this->assertSame('b-row', TenantScopedStub::first()->label);

        // Switch to A: only A rows visible.
        app(TenantManager::class)->set($tenantA);
        $this->assertCount(1, TenantScopedStub::all());
        $this->assertSame('a-row', TenantScopedStub::first()->label);
    }

    public function test_creating_a_model_auto_fills_tenant_id(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantManager::class)->set($tenant);

        $stub = TenantScopedStub::create(['label' => 'auto']);

        $this->assertSame($tenant->id, $stub->tenant_id);
    }

    public function test_without_a_tenant_scope_is_not_applied(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        app(TenantManager::class)->set($tenantA);
        TenantScopedStub::create(['label' => 'a-row']);
        app(TenantManager::class)->set($tenantB);
        TenantScopedStub::create(['label' => 'b-row']);

        // No tenant bound (central context): all rows visible.
        app(TenantManager::class)->forget();

        $this->assertCount(2, TenantScopedStub::all());
    }

    protected function tearDown(): void
    {
        \Schema::dropIfExists('tenant_scoped_stubs');

        parent::tearDown();
    }
}
