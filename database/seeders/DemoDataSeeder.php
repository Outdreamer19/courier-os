<?php

namespace Database\Seeders;

use App\Enums\PackageStatus;
use App\Enums\PaymentStatus;
use App\Enums\PreAlertStatus;
use App\Models\Package;
use App\Models\PreAlert;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()
            ->where('email', 'customer@shipdjm.test')
            ->first();

        if (! $customer) {
            return;
        }

        $submitted = PreAlert::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'tracking_number' => 'TBA123456789',
            ],
            [
                'merchant_name' => 'Amazon',
                'order_number' => '111-1234567-8901234',
                'carrier' => 'amazon_logistics',
                'expected_delivery_date' => now()->addDays(5),
                'item_description' => 'Electronics accessories — phone case and charger',
                'declared_value' => 45.99,
                'status' => PreAlertStatus::Submitted,
                'customer_notes' => 'Please match to my account when it arrives.',
            ],
        );

        $underReview = PreAlert::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'tracking_number' => '1Z999AA10123456784',
            ],
            [
                'merchant_name' => 'Walmart',
                'order_number' => 'WM-998877',
                'carrier' => 'ups',
                'expected_delivery_date' => now()->addDays(2),
                'item_description' => 'Household items',
                'declared_value' => 89.50,
                'status' => PreAlertStatus::UnderReview,
            ],
        );

        Package::query()->updateOrCreate(
            ['package_reference' => 'PKG-000001'],
            [
                'user_id' => $customer->id,
                'pre_alert_id' => $underReview->id,
                'tracking_number' => '1Z999AA10123456784',
                'merchant_name' => 'Walmart',
                'carrier' => 'ups',
                'weight_lbs' => 4.5,
                'declared_value' => 89.50,
                'amount_due' => 2250,
                'payment_status' => PaymentStatus::Unpaid,
                'status' => PackageStatus::ReadyForPickup,
                'received_at_warehouse_at' => now()->subWeeks(3),
                'shipped_to_jamaica_at' => now()->subWeeks(2),
                'arrived_in_jamaica_at' => now()->subWeek(),
                'ready_for_pickup_at' => now()->subDays(2),
                'customer_visible_notes' => 'Pickup only — bring ID and your customer reference.',
            ],
        );

        Package::query()->updateOrCreate(
            ['package_reference' => 'PKG-000002'],
            [
                'user_id' => $customer->id,
                'pre_alert_id' => $submitted->id,
                'tracking_number' => 'TBA123456789',
                'merchant_name' => 'Amazon',
                'carrier' => 'amazon_logistics',
                'amount_due' => 0,
                'payment_status' => PaymentStatus::Unpaid,
                'status' => PackageStatus::AwaitingArrival,
                'customer_visible_notes' => 'We are awaiting arrival at the Florida warehouse.',
            ],
        );

        Package::query()->updateOrCreate(
            ['package_reference' => 'PKG-000003'],
            [
                'user_id' => $customer->id,
                'tracking_number' => '9400111899223344556677',
                'merchant_name' => 'SHEIN',
                'carrier' => 'usps',
                'weight_lbs' => 2.0,
                'amount_due' => 1000,
                'payment_status' => PaymentStatus::Paid,
                'status' => PackageStatus::PickedUp,
                'received_at_warehouse_at' => now()->subMonths(2),
                'shipped_to_jamaica_at' => now()->subMonths(2)->addDays(5),
                'arrived_in_jamaica_at' => now()->subMonths(2)->addDays(12),
                'ready_for_pickup_at' => now()->subMonths(2)->addDays(14),
                'picked_up_at' => now()->subMonths(2)->addDays(16),
            ],
        );
    }
}
