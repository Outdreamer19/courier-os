<?php

namespace Database\Seeders;

use App\Enums\PackageStatus;
use App\Enums\PaymentStatus;
use App\Enums\PreAlertStatus;
use App\Models\ContactMessage;
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

        ContactMessage::query()->updateOrCreate(
            ['email' => 'guest@example.com', 'subject' => 'How do pre-alerts work?'],
            [
                'name' => 'Marcia Brown',
                'phone' => '+1 (876) 555-0200',
                'message' => 'Hi, I just signed up. Can you explain how to submit a pre-alert for my Amazon order?',
                'status' => ContactMessage::STATUS_NEW,
            ],
        );

        ContactMessage::query()->updateOrCreate(
            ['email' => 'kevin@test.com', 'subject' => 'Pickup hours'],
            [
                'name' => 'Kevin Thompson',
                'message' => 'What are your pickup hours in Kingston?',
                'status' => ContactMessage::STATUS_READ,
            ],
        );

        ContactMessage::query()->updateOrCreate(
            ['email' => 'anna@shipdjm.test', 'subject' => 'Package ready — thanks!'],
            [
                'name' => 'Anna Reid',
                'message' => 'Collected my package yesterday. Smooth process, thank you!',
                'status' => ContactMessage::STATUS_RESOLVED,
                'handled_at' => now()->subDay(),
            ],
        );

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

        $matched = PreAlert::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'tracking_number' => '9400111899223344556677',
            ],
            [
                'merchant_name' => 'SHEIN',
                'order_number' => 'SH-445566',
                'carrier' => 'usps',
                'expected_delivery_date' => now()->subWeeks(3),
                'item_description' => 'Summer clothing bundle',
                'declared_value' => 62.00,
                'status' => PreAlertStatus::MatchedToPackage,
            ],
        );

        PreAlert::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'tracking_number' => 'JD0146000030456789012',
            ],
            [
                'merchant_name' => 'Fashion Nova',
                'order_number' => 'FN-778899',
                'carrier' => 'fedex',
                'expected_delivery_date' => now()->subDays(10),
                'item_description' => 'Dress and accessories',
                'declared_value' => 120.00,
                'status' => PreAlertStatus::IssueFound,
                'admin_notes' => 'Invoice missing item line — customer contacted.',
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
                'pre_alert_id' => $matched->id,
                'tracking_number' => '9400111899223344556677',
                'merchant_name' => 'SHEIN',
                'carrier' => 'usps',
                'weight_lbs' => 2.0,
                'amount_due' => 1000,
                'payment_status' => PaymentStatus::Paid,
                'payment_method' => 'in_person',
                'paid_at' => now()->subMonths(2)->addDays(15),
                'status' => PackageStatus::PickedUp,
                'received_at_warehouse_at' => now()->subMonths(2),
                'shipped_to_jamaica_at' => now()->subMonths(2)->addDays(5),
                'arrived_in_jamaica_at' => now()->subMonths(2)->addDays(12),
                'ready_for_pickup_at' => now()->subMonths(2)->addDays(14),
                'picked_up_at' => now()->subMonths(2)->addDays(16),
            ],
        );

        Package::query()->updateOrCreate(
            ['package_reference' => 'PKG-000004'],
            [
                'user_id' => $customer->id,
                'tracking_number' => '1Z999AA10987654321',
                'merchant_name' => 'eBay',
                'carrier' => 'ups',
                'weight_lbs' => 6.2,
                'declared_value' => 155.00,
                'amount_due' => 3100,
                'payment_status' => PaymentStatus::Unpaid,
                'status' => PackageStatus::Processing,
                'received_at_warehouse_at' => now()->subDays(4),
                'customer_visible_notes' => 'Weighing in progress — final charge may adjust.',
            ],
        );
    }
}
