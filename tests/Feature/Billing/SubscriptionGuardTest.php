<?php

namespace Tests\Feature\Billing;

use App\Models\Tenant;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SubscriptionGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'courieros.co']);

        Route::middleware(['web', 'tenant.subscribed'])->get('/_guarded', fn () => 'ok');
    }

    public function test_active_tenant_passes_the_guard(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme', 'status' => Tenant::STATUS_ACTIVE]);

        $this->get('http://acme.courieros.co/_guarded')
            ->assertOk()
            ->assertSee('ok');
    }

    public function test_tenant_on_trial_passes_the_guard(): void
    {
        Tenant::factory()->create([
            'subdomain' => 'trialco',
            'status' => Tenant::STATUS_ACTIVE,
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->get('http://trialco.courieros.co/_guarded')->assertOk();
    }

    public function test_suspended_tenant_is_blocked(): void
    {
        // Suspended tenants are rejected at resolution (403) before the guard,
        // but the guard also protects routes reached in other ways.
        $tenant = Tenant::factory()->create(['subdomain' => 'frozen', 'status' => Tenant::STATUS_ACTIVE]);
        app(TenantManager::class)->set($tenant);
        $tenant->forceFill(['status' => Tenant::STATUS_SUSPENDED])->saveQuietly();

        // Re-resolve via request: suspended tenant returns 403 at middleware.
        $this->get('http://frozen.courieros.co/_guarded')->assertForbidden();
    }

    public function test_central_context_is_not_guarded(): void
    {
        // No tenant bound (central). The guard should not block platform routes.
        $this->get('http://courieros.co/_guarded')->assertOk();
    }
}
