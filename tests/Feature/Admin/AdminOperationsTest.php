<?php

namespace Tests\Feature\Admin;

use App\Enums\PackageStatus;
use App\Enums\PaymentStatus;
use App\Enums\PreAlertStatus;
use App\Models\ContactMessage;
use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\PreAlert;
use App\Models\ShippingRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function customer(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        CustomerProfile::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_admin_can_download_pre_alert_invoice(): void
    {
        Storage::fake('local');

        $customer = $this->customer();
        $path = UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf')
            ->store('invoices', 'local');

        $preAlert = PreAlert::factory()->create([
            'user_id' => $customer->id,
            'invoice_path' => $path,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.pre-alerts.invoice', ['pre_alert' => $preAlert]));

        $response->assertOk();
        $this->assertStringContainsString(
            'inline',
            (string) $response->headers->get('Content-Disposition'),
        );

        $this->actingAs($this->admin())
            ->get(route('portal.pre-alerts.invoice', ['pre_alert' => $preAlert]))
            ->assertForbidden();
    }

    public function test_admin_dashboard_includes_chart_data(): void
    {
        PreAlert::factory()->create(['status' => PreAlertStatus::Submitted]);
        PreAlert::factory()->create(['status' => PreAlertStatus::UnderReview]);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Dashboard')
                ->has('charts.new_users.labels', 6)
                ->has('charts.new_users.data', 6)
                ->has('charts.pre_alerts_by_status.labels', count(PreAlertStatus::cases()))
                ->has('charts.pre_alerts_by_status.data', count(PreAlertStatus::cases()))
            );
    }

    public function test_admin_can_access_customer_management(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.customers.index'))
            ->assertOk();
    }

    public function test_admin_can_create_a_customer(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.customers.store'), [
                'name' => 'New Customer',
                'email' => 'new@shipdjm.test',
                'password' => 'password',
                'status' => User::STATUS_ACTIVE,
            ])
            ->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new@shipdjm.test',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_admin_can_update_pre_alert_status(): void
    {
        $customer = $this->customer();
        $preAlert = PreAlert::factory()->create([
            'user_id' => $customer->id,
            'status' => PreAlertStatus::Submitted,
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.pre-alerts.update', ['pre_alert' => $preAlert]), [
                'status' => PreAlertStatus::UnderReview->value,
                'admin_notes' => 'Checking invoice',
            ])
            ->assertRedirect(route('admin.pre-alerts.show', ['pre_alert' => $preAlert]));

        $this->assertSame(
            PreAlertStatus::UnderReview,
            $preAlert->fresh()->status,
        );
    }

    public function test_admin_can_create_package_with_auto_calculated_charge(): void
    {
        ShippingRate::factory()->create([
            'rate_per_lb' => 500,
            'minimum_charge' => 500,
            'is_active' => true,
        ]);

        $customer = $this->customer();

        $this->actingAs($this->admin())
            ->post(route('admin.packages.store'), [
                'user_id' => $customer->id,
                'weight_lbs' => 4,
                'auto_calculate_amount' => true,
                'status' => PackageStatus::AwaitingArrival->value,
                'payment_status' => PaymentStatus::Unpaid->value,
            ])
            ->assertRedirect();

        $package = Package::query()->first();

        $this->assertNotNull($package);
        $this->assertSame(2000.0, (float) $package->amount_due);
    }

    public function test_admin_can_mark_package_paid(): void
    {
        $customer = $this->customer();
        $package = Package::factory()->create([
            'user_id' => $customer->id,
            'amount_due' => 1500,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.packages.update', ['package' => $package]), [
                'status' => $package->status->value,
                'payment_status' => PaymentStatus::Paid->value,
                'payment_method' => 'in_person',
                'payment_notes' => 'Paid at counter',
                'auto_calculate_amount' => false,
                'amount_due' => 1500,
            ])
            ->assertRedirect(route('admin.packages.edit', ['package' => $package]));

        $package->refresh();

        $this->assertSame(PaymentStatus::Paid, $package->payment_status);
        $this->assertNotNull($package->paid_at);
    }

    public function test_admin_can_view_contact_inbox(): void
    {
        ContactMessage::create([
            'name' => 'Guest',
            'email' => 'guest@test.com',
            'subject' => 'Hello',
            'message' => 'Need help',
            'status' => ContactMessage::STATUS_NEW,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.contact-messages.index'))
            ->assertOk();
    }

    public function test_admin_can_delete_a_contact_message(): void
    {
        $message = ContactMessage::create([
            'name' => 'Guest',
            'email' => 'guest@test.com',
            'subject' => 'Delete me',
            'message' => 'Please remove',
            'status' => ContactMessage::STATUS_NEW,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.contact-messages.destroy', ['contact_message' => $message]))
            ->assertRedirect(route('admin.contact-messages.index'));

        $this->assertDatabaseMissing('contact_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_customer_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->customer())
            ->get(route('admin.customers.index'))
            ->assertForbidden();
    }
}
