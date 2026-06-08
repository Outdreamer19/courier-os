<?php

namespace Tests\Feature\Phase4;

use App\Enums\PackageStatus;
use App\Enums\PreAlertStatus;
use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\PreAlert;
use App\Models\ShippingRate;
use App\Models\User;
use App\Models\WarehouseAddress;
use App\Notifications\PackageStatusChangedNotification;
use App\Notifications\PreAlertStatusChangedNotification;
use App\Support\ShippingRatePresenter;
use App\Support\WhatsappLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BusinessOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rate_calculator_endpoint_returns_estimate(): void
    {
        ShippingRate::factory()->create([
            'rate_per_lb' => 500,
            'minimum_charge' => 500,
            'is_active' => true,
        ]);

        $this->postJson(route('rates.calculate'), ['weight_lbs' => 4])
            ->assertOk()
            ->assertJsonPath('amount', 2000);
    }

    public function test_shipping_rate_presenter_returns_active_tiers(): void
    {
        ShippingRate::factory()->create([
            'name' => 'Tier A',
            'min_weight_lbs' => 1,
            'max_weight_lbs' => 5,
            'is_active' => true,
        ]);

        $tiers = app(ShippingRatePresenter::class)->activeTiers();

        $this->assertCount(1, $tiers);
        $this->assertSame('Tier A', $tiers[0]['name']);
    }

    public function test_owner_can_manage_shipping_rates(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.shipping-rates.index'))
            ->assertOk();

        $this->actingAs($owner)
            ->post(route('admin.shipping-rates.store'), [
                'name' => 'Heavy tier',
                'method' => 'standard',
                'currency' => 'JMD',
                'rate_per_lb' => 450,
                'minimum_charge' => 500,
                'min_weight_lbs' => 11,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.shipping-rates.index'));

        $this->assertDatabaseHas('shipping_rates', ['name' => 'Heavy tier']);
    }

    public function test_owner_can_update_warehouse_address(): void
    {
        $owner = User::factory()->owner()->create();
        $warehouse = WarehouseAddress::factory()->create(['is_active' => true]);

        $this->actingAs($owner)
            ->put(route('admin.warehouse.update', ['warehouse' => $warehouse]), [
                'name' => 'Updated Warehouse',
                'address_line_1' => '99 New Road',
                'city' => 'Miami',
                'state' => 'FL',
                'zip' => '33102',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.warehouse.index'));

        $this->assertDatabaseHas('warehouse_addresses', [
            'id' => $warehouse->id,
            'name' => 'Updated Warehouse',
        ]);
    }

    public function test_package_status_change_records_history_and_notifies_customer(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        CustomerProfile::factory()->create(['user_id' => $customer->id]);

        $package = Package::factory()->create([
            'user_id' => $customer->id,
            'status' => PackageStatus::Processing,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.packages.update', ['package' => $package]), [
                'status' => PackageStatus::ReadyForPickup->value,
                'payment_status' => $package->payment_status->value,
                'auto_calculate_amount' => false,
                'amount_due' => $package->amount_due,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('package_status_histories', [
            'package_id' => $package->id,
            'old_status' => PackageStatus::Processing->value,
            'new_status' => PackageStatus::ReadyForPickup->value,
        ]);

        Notification::assertSentTo($customer, PackageStatusChangedNotification::class);
    }

    public function test_pre_alert_status_change_notifies_customer(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        CustomerProfile::factory()->create(['user_id' => $customer->id]);

        $preAlert = PreAlert::factory()->create([
            'user_id' => $customer->id,
            'status' => PreAlertStatus::Submitted,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.pre-alerts.update', ['pre_alert' => $preAlert]), [
                'status' => PreAlertStatus::UnderReview->value,
            ])
            ->assertRedirect();

        Notification::assertSentTo(
            $customer,
            PreAlertStatusChangedNotification::class,
        );
    }

    public function test_whatsapp_link_is_built_from_phone_number(): void
    {
        $url = WhatsappLink::forPhone('+1 (876) 555-0100', 'Hello');

        $this->assertStringContainsString('https://wa.me/18765550100', $url);
        $this->assertStringContainsString('text=', $url);
    }
}
