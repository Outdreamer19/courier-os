<?php

namespace Tests\Feature\Billing;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Billable;
use Tests\TestCase;

class TenantBillableTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_uses_the_billable_trait(): void
    {
        $this->assertContains(Billable::class, class_uses_recursive(Tenant::class));
    }

    public function test_tenant_can_store_stripe_billing_columns(): void
    {
        $tenant = Tenant::factory()->create();

        $tenant->forceFill([
            'stripe_id' => 'cus_test123',
            'pm_type' => 'visa',
            'pm_last_four' => '4242',
        ])->save();

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'stripe_id' => 'cus_test123',
            'pm_type' => 'visa',
            'pm_last_four' => '4242',
        ]);
    }

    public function test_cashier_is_configured_to_bill_the_tenant_model(): void
    {
        $this->assertSame(Tenant::class, config('cashier.model'));
    }

    public function test_tenant_reports_generic_trial_status(): void
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => now()->addDays(14)]);

        $this->assertTrue($tenant->onGenericTrial());
    }
}
