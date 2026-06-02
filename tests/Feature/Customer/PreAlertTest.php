<?php

namespace Tests\Feature\Customer;

use App\Enums\PreAlertStatus;
use App\Models\CustomerProfile;
use App\Models\PreAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PreAlertTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);

        CustomerProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        return $user;
    }

    public function test_customer_can_view_pre_alerts_index(): void
    {
        $user = $this->customer();

        $this->actingAs($user)
            ->get(route('portal.pre-alerts.index'))
            ->assertOk();
    }

    public function test_customer_can_submit_a_pre_alert(): void
    {
        Storage::fake('local');

        $user = $this->customer();

        $response = $this->actingAs($user)->post(route('portal.pre-alerts.store'), [
            'merchant_name' => 'Amazon',
            'order_number' => '111-222',
            'tracking_number' => 'TRK123',
            'carrier' => 'amazon_logistics',
            'expected_delivery_date' => now()->addWeek()->toDateString(),
            'item_description' => 'Test item',
            'declared_value' => 25.50,
            'customer_notes' => 'Please match quickly',
            'invoice' => UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'),
        ]);

        $preAlert = PreAlert::query()->first();

        $response->assertRedirect(route('portal.pre-alerts.show', ['pre_alert' => $preAlert]));

        $this->assertDatabaseHas('pre_alerts', [
            'user_id' => $user->id,
            'merchant_name' => 'Amazon',
            'status' => PreAlertStatus::Submitted->value,
        ]);

        $this->assertNotNull($preAlert?->invoice_path);
        Storage::disk('local')->assertExists($preAlert->invoice_path);
    }

    public function test_customer_cannot_edit_pre_alert_after_review(): void
    {
        $user = $this->customer();

        $preAlert = PreAlert::factory()->create([
            'user_id' => $user->id,
            'status' => PreAlertStatus::MatchedToPackage,
        ]);

        $this->actingAs($user)
            ->get(route('portal.pre-alerts.edit', $preAlert))
            ->assertRedirect(route('portal.pre-alerts.show', $preAlert));

        $this->actingAs($user)
            ->put(route('portal.pre-alerts.update', $preAlert), [
                'merchant_name' => 'Changed',
                'carrier' => 'usps',
                'item_description' => 'Updated',
            ])
            ->assertForbidden();
    }

    public function test_customer_cannot_view_another_customers_pre_alert(): void
    {
        $owner = $this->customer();
        $other = $this->customer();

        $preAlert = PreAlert::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($other)
            ->get(route('portal.pre-alerts.show', $preAlert))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_customer_portal_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('portal.pre-alerts.index'))
            ->assertForbidden();
    }
}
