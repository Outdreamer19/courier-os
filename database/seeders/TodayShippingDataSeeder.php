<?php

namespace Database\Seeders;

use App\Enums\Carrier;
use App\Enums\PackageStatus;
use App\Enums\PaymentStatus;
use App\Enums\PreAlertStatus;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\PreAlert;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Fleshes out the "Today Shipping & Logistics" demo tenant with enough
 * realistic, date-spread data that the owner's dashboard charts, contact
 * inbox, activity log, and admin users pages all have something worth
 * looking at (rather than the one or two rows CourierOsSeeder leaves
 * behind).
 */
class TodayShippingDataSeeder extends Seeder
{
    private const SUBDOMAIN = 'today';

    private const PREFIX = 'TSL';

    public function run(): void
    {
        // Guarantees the tenant, owner, warehouse, and starter rate exist.
        $this->call(CourierOsSeeder::class);

        $manager = app(TenantManager::class);
        $manager->forget();

        $tenant = Tenant::query()->where('subdomain', self::SUBDOMAIN)->firstOrFail();
        $manager->set($tenant);

        $admin = $this->seedAdminTeam();
        $customers = $this->seedCustomers();

        // seedCustomers/seedContactMessages are idempotent (updateOrCreate),
        // but the pre-alert/package/activity-log generators are not — guard
        // them so re-running the seeder on an already-seeded database
        // doesn't duplicate records or collide on package_reference.
        if (PreAlert::query()->count() > 1) {
            $preAlerts = [];
            $packages = [];
        } else {
            [$preAlerts, $packages] = $this->seedPreAlertsAndPackages($customers);
        }

        $this->seedContactMessages();

        if (ActivityLog::query()->count() < 3) {
            $this->seedActivityLogs($admin, $customers, $preAlerts, $packages);
        }

        $manager->forget();
    }

    /**
     * @return array{owner: User, admin: User, staff: User}
     */
    private function seedAdminTeam(): array
    {
        $owner = User::query()->where('email', 'owner@today.test')->first();

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@today.test'],
            [
                'name' => 'Kimberly Wright',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
        );

        $staff = User::query()->updateOrCreate(
            ['email' => 'staff@today.test'],
            [
                'name' => 'Rohan Fletcher',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STAFF,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
        );

        return ['owner' => $owner, 'admin' => $admin, 'staff' => $staff];
    }

    /**
     * @return list<User>
     */
    private function seedCustomers(): array
    {
        $roster = [
            ['name' => 'Shauna Reid', 'parish' => 'Kingston', 'daysAgo' => 5],
            ['name' => 'Odane Wilson', 'parish' => 'St. Andrew', 'daysAgo' => 12],
            ['name' => 'Kadeen Powell', 'parish' => 'St. Catherine', 'daysAgo' => 18],
            ['name' => 'Tremaine Foster', 'parish' => 'Clarendon', 'daysAgo' => 26],
            ['name' => 'Latoya Grant', 'parish' => 'Manchester', 'daysAgo' => 34],
            ['name' => 'Jermaine Palmer', 'parish' => 'St. James', 'daysAgo' => 41],
            ['name' => 'Alecia Simmonds', 'parish' => 'Portland', 'daysAgo' => 49],
            ['name' => 'Rickardo Chambers', 'parish' => 'St. Ann', 'daysAgo' => 58],
            ['name' => 'Nordia Blake', 'parish' => 'Westmoreland', 'daysAgo' => 67],
            ['name' => 'Devon McKenzie', 'parish' => 'St. Thomas', 'daysAgo' => 79],
            ['name' => 'Simone Bailey', 'parish' => 'Trelawny', 'daysAgo' => 93],
            ['name' => 'Orville Dixon', 'parish' => 'St. Mary', 'daysAgo' => 108],
            ['name' => 'Petagaye Morrison', 'parish' => 'Hanover', 'daysAgo' => 126],
            ['name' => 'Dwayne Anderson', 'parish' => 'St. Elizabeth', 'daysAgo' => 149],
            ['name' => 'Kerry-Ann Thomas', 'parish' => 'Kingston', 'daysAgo' => 172],
        ];

        $customers = [];

        foreach ($roster as $index => $entry) {
            $slug = str_replace(' ', '.', mb_strtolower($entry['name']));
            $createdAt = now()->subDays($entry['daysAgo']);
            $sequence = str_pad((string) ($index + 2), 6, '0', STR_PAD_LEFT);

            $customer = User::query()->updateOrCreate(
                ['email' => "{$slug}@today.test"],
                [
                    'name' => $entry['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_CUSTOMER,
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => $createdAt,
                ],
            );
            $customer->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

            CustomerProfile::query()->updateOrCreate(
                ['user_id' => $customer->id],
                [
                    'customer_reference' => self::PREFIX.'-'.$sequence,
                    'trn' => (string) random_int(100000000, 999999999),
                    'date_of_birth' => now()->subYears(random_int(20, 55))->subDays(random_int(0, 365)),
                    'phone' => '+1 (876) 555-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                    'whatsapp_number' => '+1 (876) 555-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                    'jamaica_address' => random_int(1, 200).' '.$entry['parish'].' Main Road',
                    'parish' => $entry['parish'],
                ],
            );

            $customers[] = $customer;
        }

        return $customers;
    }

    /**
     * @param  list<User>  $customers
     * @return array{0: list<PreAlert>, 1: list<Package>}
     */
    private function seedPreAlertsAndPackages(array $customers): array
    {
        $merchants = ['Amazon', 'SHEIN', 'Walmart', 'eBay', 'Fashion Nova', 'Target', 'Best Buy'];
        $carriers = Carrier::cases();
        $preAlertStatuses = PreAlertStatus::cases();

        $packageStatusRecipe = [
            PackageStatus::AwaitingArrival,
            PackageStatus::ReceivedAtFloridaWarehouse,
            PackageStatus::Processing,
            PackageStatus::InTransitToJamaica,
            PackageStatus::ArrivedInJamaica,
            PackageStatus::CustomsProcessing,
            PackageStatus::ReadyForPickup,
            PackageStatus::ReadyForPickup,
            PackageStatus::PickedUp,
            PackageStatus::PickedUp,
            PackageStatus::PickedUp,
            PackageStatus::OnHold,
        ];

        $progression = [
            PackageStatus::AwaitingArrival,
            PackageStatus::ReceivedAtFloridaWarehouse,
            PackageStatus::Processing,
            PackageStatus::InTransitToJamaica,
            PackageStatus::ArrivedInJamaica,
            PackageStatus::CustomsProcessing,
            PackageStatus::ReadyForPickup,
            PackageStatus::PickedUp,
        ];

        $preAlerts = [];
        $packages = [];
        $ratePerLb = 750; // JMD, matches Today Shipping's seeded rate.
        $packageCounter = Package::query()->count() + 1; // Continue after CourierOsSeeder's PKG-TSL-001/002.

        foreach ($customers as $index => $customer) {
            $itemsForCustomer = 1 + ($index % 3); // 1–3 pre-alerts/packages per customer

            for ($i = 0; $i < $itemsForCustomer; $i++) {
                $daysAgo = random_int(1, 165);
                $createdAt = now()->subDays($daysAgo);
                $merchant = $merchants[array_rand($merchants)];
                $carrier = $carriers[array_rand($carriers)];
                $trackingNumber = strtoupper(self::PREFIX.'-TRK-'.random_int(100000, 999999));

                $preAlertStatus = $preAlertStatuses[array_rand($preAlertStatuses)];

                $preAlert = PreAlert::query()->create([
                    'user_id' => $customer->id,
                    'merchant_name' => $merchant,
                    'order_number' => strtoupper(substr($merchant, 0, 2)).'-'.random_int(100000, 999999),
                    'tracking_number' => $trackingNumber,
                    'carrier' => $carrier->value,
                    'expected_delivery_date' => $createdAt->copy()->addDays(random_int(3, 10)),
                    'item_description' => $this->randomItemDescription(),
                    'declared_value' => random_int(20, 400),
                    'status' => $preAlertStatus,
                ]);
                $preAlert->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
                $preAlerts[] = $preAlert;

                // Not every pre-alert has matured into a package yet.
                if (! in_array($preAlertStatus, [PreAlertStatus::Submitted, PreAlertStatus::UnderReview, PreAlertStatus::Cancelled], true)) {
                    $status = $packageStatusRecipe[array_rand($packageStatusRecipe)];

                    // OnHold packages are treated as "held during customs" for
                    // the purpose of deciding which timestamps are populated.
                    $stageStatus = $status === PackageStatus::OnHold ? PackageStatus::CustomsProcessing : $status;
                    $stage = array_search($stageStatus, $progression, true);
                    $stage = $stage === false ? 0 : $stage;

                    $hasBeenWeighed = $stage >= 1;
                    $weight = $hasBeenWeighed ? round(random_int(10, 220) / 10, 2) : null;
                    $amountDue = $hasBeenWeighed ? (int) max($ratePerLb, round($weight * $ratePerLb)) : 0;
                    $paid = $status === PackageStatus::PickedUp && random_int(0, 9) > 1;

                    $package = Package::query()->create([
                        'user_id' => $customer->id,
                        'pre_alert_id' => $preAlert->id,
                        'package_reference' => 'PKG-'.self::PREFIX.'-'.str_pad((string) $packageCounter, 3, '0', STR_PAD_LEFT),
                        'tracking_number' => $trackingNumber,
                        'merchant_name' => $merchant,
                        'carrier' => $carrier->value,
                        'weight_lbs' => $weight,
                        'declared_value' => $preAlert->declared_value,
                        'amount_due' => $amountDue,
                        'payment_status' => $paid ? PaymentStatus::Paid : PaymentStatus::Unpaid,
                        'payment_method' => $paid ? 'in_person' : null,
                        'paid_at' => $paid ? $createdAt->copy()->addDays(random_int(11, 20)) : null,
                        'status' => $status,
                        'received_at_warehouse_at' => $stage >= 1 ? $createdAt->copy()->addDays(1) : null,
                        'shipped_to_jamaica_at' => $stage >= 3 ? $createdAt->copy()->addDays(4) : null,
                        'arrived_in_jamaica_at' => $stage >= 4 ? $createdAt->copy()->addDays(8) : null,
                        'ready_for_pickup_at' => $stage >= 6 ? $createdAt->copy()->addDays(10) : null,
                        'picked_up_at' => $status === PackageStatus::PickedUp
                            ? $createdAt->copy()->addDays(random_int(11, 16)) : null,
                        'customer_visible_notes' => $this->noteForStatus($status),
                    ]);
                    $packageCreatedAt = $createdAt->copy()->addDays(1);
                    $package->forceFill(['created_at' => $packageCreatedAt, 'updated_at' => $packageCreatedAt])->save();
                    $packages[] = $package;
                    $packageCounter++;
                }
            }
        }

        return [$preAlerts, $packages];
    }

    private function seedContactMessages(): void
    {
        $messages = [
            ['name' => 'Shauna Reid', 'email' => 'shauna.reid@today.test', 'subject' => 'How do I submit a pre-alert?', 'message' => 'Hi, I just signed up with Today Shipping. Can someone walk me through submitting my first pre-alert for an Amazon order?', 'status' => ContactMessage::STATUS_NEW, 'daysAgo' => 1],
            ['name' => 'Odane Wilson', 'email' => 'odane.wilson@today.test', 'subject' => 'Package stuck in customs processing', 'message' => 'My package PKG-TSL has been in "Customs Processing" for over a week. Can you check on the status for me?', 'status' => ContactMessage::STATUS_NEW, 'daysAgo' => 2],
            ['name' => 'Kadeen Powell', 'email' => 'kadeen.powell@today.test', 'subject' => 'Pickup hours this Saturday', 'message' => 'Are you open for package pickup this Saturday? I work during the week and can only collect on weekends.', 'status' => ContactMessage::STATUS_NEW, 'daysAgo' => 3],
            ['name' => 'Tremaine Foster', 'email' => 'tremaine.foster@today.test', 'subject' => 'Wrong weight charged on my package', 'message' => 'I think the weight on my last package was recorded incorrectly — the charge seems too high for a phone case. Can someone review it?', 'status' => ContactMessage::STATUS_READ, 'daysAgo' => 6],
            ['name' => 'Latoya Grant', 'email' => 'latoya.grant@today.test', 'subject' => 'How to update my shipping address', 'message' => 'I need to update the suite number on my Florida shipping address — I think I typed it wrong when I registered.', 'status' => ContactMessage::STATUS_READ, 'daysAgo' => 9],
            ['name' => 'Jermaine Palmer', 'email' => 'jermaine.palmer@today.test', 'subject' => 'Question about declared value', 'message' => 'Does the declared value I enter on a pre-alert affect my shipping cost, or is it only used for customs and insurance?', 'status' => ContactMessage::STATUS_READ, 'daysAgo' => 14],
            ['name' => 'Alecia Simmonds', 'email' => 'alecia.simmonds@today.test', 'subject' => 'Great service, thank you!', 'message' => 'Just wanted to say thanks — my package arrived faster than expected and pickup was quick and easy.', 'status' => ContactMessage::STATUS_RESOLVED, 'daysAgo' => 20],
            ['name' => 'Rickardo Chambers', 'email' => 'rickardo.chambers@today.test', 'subject' => 'Can I add an authorised pickup person?', 'message' => 'I will be travelling when my package is ready. Can I authorise my brother to collect it on my behalf?', 'status' => ContactMessage::STATUS_RESOLVED, 'daysAgo' => 27],
            ['name' => 'Nordia Blake', 'email' => 'nordia.blake@today.test', 'subject' => 'Missing tracking number', 'message' => 'I submitted a pre-alert but forgot to include the tracking number. How do I add it now?', 'status' => ContactMessage::STATUS_RESOLVED, 'daysAgo' => 35],
            ['name' => 'Guest Visitor', 'email' => 'guest.visitor@example.com', 'subject' => 'Do you ship to other parishes?', 'message' => 'I live in Negril — does Today Shipping deliver there, or is it pickup-only from a Kingston location?', 'status' => ContactMessage::STATUS_NEW, 'daysAgo' => 0],
        ];

        foreach ($messages as $entry) {
            $createdAt = now()->subDays($entry['daysAgo']);

            $message = ContactMessage::query()->updateOrCreate(
                ['email' => $entry['email'], 'subject' => $entry['subject']],
                [
                    'name' => $entry['name'],
                    'phone' => '+1 (876) 555-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                    'message' => $entry['message'],
                    'status' => $entry['status'],
                    'handled_at' => $entry['status'] === ContactMessage::STATUS_RESOLVED
                        ? $createdAt->copy()->addDay() : null,
                ],
            );
            $message->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }
    }

    /**
     * @param  array{owner: User, admin: User, staff: User}  $admin
     * @param  list<User>  $customers
     * @param  list<PreAlert>  $preAlerts
     * @param  list<Package>  $packages
     */
    private function seedActivityLogs(array $admin, array $customers, array $preAlerts, array $packages): void
    {
        $entries = [
            ['user' => $admin['owner'], 'action' => ActivityLog::ACTION_ADMIN_USER_CREATED, 'description' => "{$admin['owner']->name} created admin user {$admin['admin']->name} (admin).", 'daysAgo' => 170],
            ['user' => $admin['owner'], 'action' => ActivityLog::ACTION_ADMIN_USER_CREATED, 'description' => "{$admin['owner']->name} created admin user {$admin['staff']->name} (staff).", 'daysAgo' => 168],
            ['user' => $customers[0] ?? null, 'action' => ActivityLog::ACTION_CUSTOMER_REGISTERED, 'description' => ($customers[0]?->name ?? 'A customer').' registered a new account.', 'daysAgo' => 5],
            ['user' => $customers[1] ?? null, 'action' => ActivityLog::ACTION_CUSTOMER_PROFILE_UPDATED, 'description' => ($customers[1]?->name ?? 'A customer').' updated their shipping address.', 'daysAgo' => 11],
            ['user' => $customers[2] ?? null, 'action' => ActivityLog::ACTION_PRE_ALERT_CREATED, 'description' => ($customers[2]?->name ?? 'A customer').' submitted a new pre-alert.', 'daysAgo' => 17, 'subject' => $preAlerts[0] ?? null],
            ['user' => $admin['staff'], 'action' => ActivityLog::ACTION_PRE_ALERT_STATUS_CHANGED, 'description' => "{$admin['staff']->name} moved a pre-alert to Under Review.", 'daysAgo' => 15, 'subject' => $preAlerts[1] ?? null],
            ['user' => $admin['admin'], 'action' => ActivityLog::ACTION_PACKAGE_CREATED, 'description' => "{$admin['admin']->name} created a package from a matched pre-alert.", 'daysAgo' => 12, 'subject' => $packages[0] ?? null],
            ['user' => $admin['staff'], 'action' => ActivityLog::ACTION_PACKAGE_STATUS_CHANGED, 'description' => "{$admin['staff']->name} marked a package as Ready for Pickup.", 'daysAgo' => 9, 'subject' => $packages[1] ?? null],
            ['user' => $admin['admin'], 'action' => ActivityLog::ACTION_BILLING_INVOICE_GENERATED, 'description' => "{$admin['admin']->name} generated an invoice for a picked-up package.", 'daysAgo' => 7, 'subject' => $packages[2] ?? null],
            ['user' => $admin['owner'], 'action' => ActivityLog::ACTION_BILLING_PAYMENT_SYNCED, 'description' => "{$admin['owner']->name} synced payment status from InvoiceFeed.", 'daysAgo' => 4],
            ['user' => $admin['admin'], 'action' => ActivityLog::ACTION_RECORD_DELETED, 'description' => "{$admin['admin']->name} deleted a duplicate contact message.", 'daysAgo' => 2],
            ['user' => $admin['owner'], 'action' => ActivityLog::ACTION_CUSTOMER_UPDATED, 'description' => "{$admin['owner']->name} updated a customer's TRN on file.", 'daysAgo' => 1],
        ];

        foreach ($entries as $entry) {
            $user = $entry['user'];
            $subject = $entry['subject'] ?? null;
            $createdAt = now()->subDays($entry['daysAgo']);

            $log = ActivityLog::query()->create([
                'user_id' => $user?->id,
                'action' => $entry['action'],
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->id,
                'description' => $entry['description'],
            ]);
            $log->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }
    }

    private function randomItemDescription(): string
    {
        $descriptions = [
            'Wireless earbuds and charging case',
            'Running shoes, size 9',
            'Kitchen blender and accessories',
            'Two bath towels and a bath mat',
            'Laptop sleeve and USB hub',
            'Skincare bundle — cleanser and moisturiser',
            'Kids backpack and lunch box',
            'Bluetooth speaker',
            'Set of phone cases and screen protectors',
            'Hair dryer and styling tools',
            'Two pairs of jeans',
            'Board game and puzzle set',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function noteForStatus(PackageStatus $status): string
    {
        return match ($status) {
            PackageStatus::ReadyForPickup => 'Ready for pickup. Bring ID and your customer reference.',
            PackageStatus::PickedUp => 'Collected — thank you for shipping with us.',
            PackageStatus::OnHold => 'On hold — our team will reach out with details.',
            PackageStatus::CustomsProcessing => 'Currently clearing customs in Jamaica.',
            PackageStatus::InTransitToJamaica => 'On its way to Jamaica.',
            default => 'We will update you as your package moves through our warehouse.',
        };
    }
}
