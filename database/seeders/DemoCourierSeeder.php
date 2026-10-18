<?php

namespace Database\Seeders;

use App\Enums\Carrier;
use App\Enums\PackageStatus;
use App\Enums\PaymentStatus;
use App\Enums\PreAlertStatus;
use App\Models\ActivityLog;
use App\Models\AuthorisedPickupPerson;
use App\Models\ContactMessage;
use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\PackageStatusHistory;
use App\Models\PreAlert;
use App\Models\ShippingRate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WarehouseAddress;
use App\Support\Demo\DemoMode;
use App\Support\Tenancy\TenantManager;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Seeds "Blue Mahoe Express", the fictional courier behind the public demo
 * at demo.{central_domain}. Every name, address and number here is made up.
 *
 * Dates are relative to now, so the dashboards look current on any day, and
 * the random choices use a fixed seed, so every nightly reset produces the
 * same demo. Run through `php artisan demo:reset`, which wipes the old demo
 * data first; on its own this seeder expects the demo tenant not to exist.
 */
class DemoCourierSeeder extends Seeder
{
    public const NAME = DemoMode::COURIER_NAME;

    public const PREFIX = 'BME';

    public const LOGO_PATH = 'demo/blue-mahoe-logo.svg';

    private const RATE_PER_LB = 650; // JMD

    private Randomizer $random;

    private int $packageCounter = 1;

    /** @var array{owner: User, admin: User, staff: User} */
    private array $team;

    public function run(): void
    {
        $this->random = new Randomizer(new Mt19937(2026));
        $this->packageCounter = 1;

        $manager = app(TenantManager::class);
        $manager->forget();

        $tenant = $this->createTenant();
        $manager->set($tenant);

        try {
            $this->seedWarehouseAndRates();
            $this->team = $this->seedTeam();
            $demoCustomer = $this->seedDemoCustomer();
            $customers = $this->seedCustomers();
            $this->seedDemoCustomerShipments($demoCustomer);
            $this->seedOtherShipments($customers);
            $this->seedContactMessages();
            $this->seedActivity($demoCustomer, $customers);
        } finally {
            $manager->forget();
        }
    }

    private function createTenant(): Tenant
    {
        $this->publishLogo();

        return Tenant::query()->create([
            'name' => self::NAME,
            'subdomain' => (string) config('courieros.demo.subdomain', 'demo'),
            'status' => Tenant::STATUS_ACTIVE,
            'currency' => 'JMD',
            'customer_reference_prefix' => self::PREFIX,
            'package_reference_prefix' => 'PKG',
            'whatsapp_number' => '+1 (876) 555-0142',
            'logo_path' => self::LOGO_PATH,
            'brand_primary_color' => '#1F5F6B',
            'brand_accent_color' => '#F2A93B',
            // Never billed: the demo is ours. A far trial keeps it entitled
            // under the subscription guard without a Stripe customer.
            'trial_ends_at' => now()->addYears(50),
        ]);
    }

    private function publishLogo(): void
    {
        $source = public_path('images/demo/blue-mahoe-logo.svg');

        if (is_file($source)) {
            Storage::disk('public')->put(self::LOGO_PATH, (string) file_get_contents($source));
        }
    }

    private function seedWarehouseAndRates(): void
    {
        WarehouseAddress::query()->create([
            'name' => self::NAME.' Miami',
            'address_line_1' => '1450 Mahoe Way',
            'address_line_2' => 'Suite '.self::PREFIX,
            'city' => 'Doral',
            'state' => 'FL',
            'zip' => '33172',
            'phone' => '+1 (305) 555-0188',
            'instructions' => 'Put your customer number (for example BME-000001) after the suite on every order so we can match your package fast.',
            'is_active' => true,
        ]);

        ShippingRate::query()->create([
            'name' => 'Standard Air',
            'method' => 'standard',
            'currency' => 'JMD',
            'rate_per_lb' => self::RATE_PER_LB,
            'minimum_charge' => self::RATE_PER_LB,
            'handling_fee' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * @return array{owner: User, admin: User, staff: User}
     */
    private function seedTeam(): array
    {
        $since = now()->subMonths(7);

        return [
            'owner' => $this->user('Andrea Lyn', (string) config('courieros.demo.admin_email'), User::ROLE_OWNER, $since, demoLogin: true),
            'admin' => $this->user('Marlon Brown', 'marlon@bluemahoe.example', User::ROLE_ADMIN, $since->addDays(2)),
            'staff' => $this->user('Kerine Shaw', 'kerine@bluemahoe.example', User::ROLE_STAFF, $since->addDays(9)),
        ];
    }

    private function seedDemoCustomer(): User
    {
        $customer = $this->user(
            'Tanisha Campbell',
            (string) config('courieros.demo.customer_email'),
            User::ROLE_CUSTOMER,
            now()->subMonths(5),
            demoLogin: true,
        );

        $profile = CustomerProfile::query()->create([
            'user_id' => $customer->id,
            'customer_reference' => self::PREFIX.'-000001',
            'trn' => '100200300',
            'date_of_birth' => '1993-04-17',
            'phone' => '+1 (876) 555-0101',
            'whatsapp_number' => '+1 (876) 555-0101',
            'jamaica_address' => '22 Hope Road',
            'parish' => 'St. Andrew',
        ]);

        AuthorisedPickupPerson::query()->create([
            'customer_profile_id' => $profile->id,
            'full_name' => 'Kemar Campbell',
            'phone' => '+1 (876) 555-0102',
            'relationship_note' => 'Brother',
        ]);

        return $customer;
    }

    /**
     * @return list<User>
     */
    private function seedCustomers(): array
    {
        $roster = [
            ['Shanice Morgan', 'Kingston', 3], ['Rajeev Singh', 'St. Andrew', 6],
            ['Monique Henry', 'St. Catherine', 9], ['Ricardo Gordon', 'Clarendon', 13],
            ['Kimone Francis', 'Manchester', 17], ['Damion Walters', 'St. James', 22],
            ['Tashana Lewis', 'Portland', 28], ['Andre Spence', 'St. Ann', 33],
            ['Jodi-Ann Richards', 'Westmoreland', 39], ['Garfield Mitchell', 'St. Thomas', 46],
            ['Crystal Ennis', 'Trelawny', 54], ['Omar Bennett', 'St. Mary', 61],
            ['Sasha-Gaye Hall', 'Hanover', 69], ['Courtney Brown', 'St. Elizabeth', 78],
            ['Natalie Chin', 'Kingston', 86], ['Dwight Samuels', 'St. Andrew', 95],
            ['Keisha Harvey', 'St. Catherine', 104], ['Leon Murray', 'Clarendon', 113],
            ['Rochelle Daley', 'Manchester', 124], ['Sheldon Barrett', 'St. James', 135],
            ['Yanique Coore', 'St. Ann', 147], ['Patrick Ellis', 'Portland', 158],
            ['Toni-Ann Wright', 'Kingston', 171], ['Marvin Edwards', 'St. Andrew', 184],
        ];

        $customers = [];

        foreach ($roster as $index => [$name, $parish, $daysAgo]) {
            $slug = strtolower(str_replace([' ', '-'], ['.', ''], $name));
            $customer = $this->user($name, "{$slug}@example.com", User::ROLE_CUSTOMER, now()->subDays($daysAgo));

            CustomerProfile::query()->create([
                'user_id' => $customer->id,
                'customer_reference' => self::PREFIX.'-'.str_pad((string) ($index + 2), 6, '0', STR_PAD_LEFT),
                'trn' => (string) $this->random->getInt(100000000, 999999999),
                'date_of_birth' => now()->subYears($this->random->getInt(21, 58))->subDays($this->random->getInt(0, 364))->toDateString(),
                'phone' => $this->phone(),
                'whatsapp_number' => $this->phone(),
                'jamaica_address' => $this->random->getInt(1, 180).' '.$this->pick(['Main Street', 'Church Street', 'Hope Road', 'Market Street', 'Old Hope Road', 'Barbican Road']),
                'parish' => $parish,
            ]);

            $customers[] = $customer;
        }

        return $customers;
    }

    /**
     * The login visitors use, so it gets a hand-picked spread: something to
     * pay and collect, something on the way, history, and open pre-alerts.
     */
    private function seedDemoCustomerShipments(User $customer): void
    {
        $this->shipment($customer, 'Amazon', Carrier::AmazonLogistics, 'Wireless headphones and a phone charger', 89.99, PackageStatus::ReadyForPickup, 9, 3.4);
        $this->shipment($customer, 'SHEIN', Carrier::Usps, 'Summer dresses and two tops', 64.50, PackageStatus::InTransitToJamaica, 5, 2.1);
        $this->shipment($customer, 'Walmart', Carrier::Fedex, 'Rice cooker', 49.00, PackageStatus::ReceivedAtFloridaWarehouse, 2, 7.8);
        $this->shipment($customer, 'Best Buy', Carrier::Ups, 'Laptop stand and keyboard', 129.00, PackageStatus::PickedUp, 34, 5.2, paid: true);
        $this->shipment($customer, 'Target', Carrier::Ups, 'School supplies', 42.75, PackageStatus::PickedUp, 61, 4.0, paid: true);

        $this->preAlert($customer, 'Amazon', Carrier::AmazonLogistics, 'Air fryer', 99.00, PreAlertStatus::Submitted, 1, expectedInDays: 3);
        $this->preAlert($customer, 'Fashion Nova', Carrier::Usps, 'Jeans and a jacket', 85.00, PreAlertStatus::UnderReview, 2, expectedInDays: 1);
        $this->preAlert($customer, 'eBay', Carrier::Usps, 'Vintage camera lens', 140.00, PreAlertStatus::IssueFound, 6, expectedInDays: -2,
            adminNotes: 'The invoice shows a different item to the description. Please upload the correct invoice.');
    }

    /**
     * @param  list<User>  $customers
     */
    private function seedOtherShipments(array $customers): void
    {
        $merchants = ['Amazon', 'SHEIN', 'Walmart', 'eBay', 'Fashion Nova', 'Target', 'Best Buy', 'Temu', 'Old Navy'];
        $items = [
            'Wireless earbuds', 'Running shoes, size 9', 'Blender and accessories', 'Bath towels', 'Laptop sleeve and USB hub',
            'Skincare bundle', 'Kids backpack and lunch box', 'Bluetooth speaker', 'Phone cases and screen protectors',
            'Hair dryer and styling tools', 'Two pairs of jeans', 'Board games', 'Car phone mount', 'Vitamins and supplements',
            'Baby clothes', 'Printer ink', 'Ring light', 'Gym gloves and resistance bands',
        ];

        // Weighted so the admin board has a believable mix: more packages
        // collected and on the way than stuck.
        $recipe = [
            PackageStatus::AwaitingArrival, PackageStatus::ReceivedAtFloridaWarehouse, PackageStatus::ReceivedAtFloridaWarehouse,
            PackageStatus::Processing, PackageStatus::InTransitToJamaica, PackageStatus::InTransitToJamaica,
            PackageStatus::ArrivedInJamaica, PackageStatus::CustomsProcessing, PackageStatus::ReadyForPickup,
            PackageStatus::ReadyForPickup, PackageStatus::ReadyForPickup, PackageStatus::PickedUp,
        ];

        foreach ($customers as $index => $customer) {
            // History first: collected packages spread over the last six
            // months, so the charts show a courier that has been trading a
            // while rather than one that started this week.
            $history = 3 + ($index % 2);

            for ($i = 0; $i < $history; $i++) {
                $daysAgo = $this->random->getInt(14, 178);

                $this->shipment(
                    $customer,
                    $this->pick($merchants),
                    $this->pick(Carrier::cases()),
                    $this->pick($items),
                    (float) $this->random->getInt(20, 380),
                    PackageStatus::PickedUp,
                    $daysAgo,
                    $this->random->getInt(8, 240) / 10,
                    paid: true,
                );
            }

            $count = 1 + ($index % 2);

            for ($i = 0; $i < $count; $i++) {
                $status = $this->pick($recipe);
                $daysAgo = $this->daysAgoFor($status);

                $this->shipment(
                    $customer,
                    $this->pick($merchants),
                    $this->pick(Carrier::cases()),
                    $this->pick($items),
                    (float) $this->random->getInt(20, 380),
                    $status,
                    $daysAgo,
                    $this->random->getInt(8, 240) / 10,
                    paid: $status === PackageStatus::PickedUp || ($status === PackageStatus::ReadyForPickup && $this->random->getInt(0, 2) === 0),
                );
            }

            // A few open pre-alerts waiting on arrival.
            if ($index % 3 === 0) {
                $this->preAlert($customer, $this->pick($merchants), $this->pick(Carrier::cases()), $this->pick($items),
                    (float) $this->random->getInt(25, 220), $this->pick([PreAlertStatus::Submitted, PreAlertStatus::UnderReview]),
                    $this->random->getInt(0, 4), expectedInDays: $this->random->getInt(1, 6));
            }
        }

        // One package on hold, so the admin sees how exceptions look
        // without the board looking troubled.
        $this->shipment($customers[4], 'Amazon', Carrier::AmazonLogistics, 'Drone with spare batteries', 310.00, PackageStatus::OnHold, 6, 4.6);
    }

    private function seedContactMessages(): void
    {
        $messages = [
            ['Guest visitor', 'guest@example.com', 'Do you deliver to Negril?', 'Hi, I live in Negril. Do you deliver there or is it pickup only in Kingston?', ContactMessage::STATUS_NEW, 0],
            ['Shanice Morgan', 'shanice.morgan@example.com', 'How do I send a pre-alert?', 'I just signed up. How do I tell you about my Amazon order so you can match it?', ContactMessage::STATUS_NEW, 1],
            ['Ricardo Gordon', 'ricardo.gordon@example.com', 'Saturday pickup', 'Are you open for pickup on Saturday? I work all week.', ContactMessage::STATUS_NEW, 2],
            ['Kimone Francis', 'kimone.francis@example.com', 'Weight question', 'My package says 6.2 lb but the box was small. Can someone check it please?', ContactMessage::STATUS_READ, 5],
            ['Tashana Lewis', 'tashana.lewis@example.com', 'Thank you', 'Package came faster than I expected. Pickup was quick. Thanks a lot!', ContactMessage::STATUS_RESOLVED, 12],
        ];

        foreach ($messages as [$name, $email, $subject, $body, $status, $daysAgo]) {
            $createdAt = now()->subDays($daysAgo)->subHours($this->random->getInt(1, 9));

            $message = ContactMessage::query()->create([
                'name' => $name,
                'email' => $email,
                'phone' => $this->phone(),
                'subject' => $subject,
                'message' => $body,
                'status' => $status,
                'handled_at' => $status === ContactMessage::STATUS_RESOLVED ? $createdAt->addDay() : null,
            ]);
            $this->backdate($message, $createdAt);
        }
    }

    /**
     * @param  list<User>  $customers
     */
    private function seedActivity(User $demoCustomer, array $customers): void
    {
        $owner = $this->team['owner'];
        $admin = $this->team['admin'];
        $staff = $this->team['staff'];

        $entries = [
            [$owner, ActivityLog::ACTION_ADMIN_USER_CREATED, "{$owner->name} added {$admin->name} as an admin.", 200],
            [$owner, ActivityLog::ACTION_ADMIN_USER_CREATED, "{$owner->name} added {$staff->name} as staff.", 193],
            [$customers[0], ActivityLog::ACTION_CUSTOMER_REGISTERED, "{$customers[0]->name} created an account.", 3],
            [$demoCustomer, ActivityLog::ACTION_PRE_ALERT_CREATED, "{$demoCustomer->name} sent a pre-alert for an Amazon order.", 1],
            [$staff, ActivityLog::ACTION_PACKAGE_STATUS_CHANGED, "{$staff->name} marked a package Ready for Pickup.", 1],
            [$admin, ActivityLog::ACTION_PRE_ALERT_STATUS_CHANGED, "{$admin->name} flagged a pre-alert with an invoice issue.", 6],
            [$staff, ActivityLog::ACTION_PACKAGE_CREATED, "{$staff->name} received a package at the Miami warehouse.", 2],
            [$owner, ActivityLog::ACTION_CUSTOMER_UPDATED, "{$owner->name} updated a customer's phone number.", 8],
        ];

        foreach ($entries as [$user, $action, $description, $daysAgo]) {
            $log = ActivityLog::query()->create([
                'user_id' => $user->id,
                'action' => $action,
                'description' => $description,
            ]);
            $this->backdate($log, now()->subDays($daysAgo)->subHours($this->random->getInt(1, 8)));
        }
    }

    private function shipment(
        User $customer,
        string $merchant,
        Carrier $carrier,
        string $item,
        float $value,
        PackageStatus $status,
        int $daysAgo,
        float $weight,
        bool $paid = false,
    ): Package {
        $orderedAt = now()->subDays($daysAgo)->subHours($this->random->getInt(1, 10));
        $tracking = $this->trackingNumber($carrier);

        $preAlert = $this->preAlert($customer, $merchant, $carrier, $item, $value,
            $status === PackageStatus::PickedUp ? PreAlertStatus::Completed : PreAlertStatus::MatchedToPackage,
            $daysAgo, tracking: $tracking);

        $stage = $this->stageOf($status);
        $weighed = $stage >= 1;
        $amountDue = $weighed ? (int) max(self::RATE_PER_LB, round($weight * self::RATE_PER_LB)) : 0;
        $timeline = $this->timeline($orderedAt, $stage, $status);
        $paidAt = $paid ? ($timeline['picked_up_at'] ?? $timeline['ready_for_pickup_at'] ?? $orderedAt->addDays(10)) : null;

        $package = Package::query()->create([
            'user_id' => $customer->id,
            'pre_alert_id' => $preAlert->id,
            'package_reference' => 'PKG-'.self::PREFIX.'-'.str_pad((string) $this->packageCounter++, 4, '0', STR_PAD_LEFT),
            'tracking_number' => $tracking,
            'merchant_name' => $merchant,
            'carrier' => $carrier->value,
            'weight_lbs' => $weighed ? round($weight, 1) : null,
            'declared_value' => $value,
            'amount_due' => $amountDue,
            'payment_status' => $paid ? PaymentStatus::Paid : PaymentStatus::Unpaid,
            'payment_method' => $paid ? $this->pick(['in_person', 'online']) : null,
            'paid_at' => $paidAt,
            'status' => $status,
            'received_at_warehouse_at' => $timeline['received_at_warehouse_at'] ?? null,
            'shipped_to_jamaica_at' => $timeline['shipped_to_jamaica_at'] ?? null,
            'arrived_in_jamaica_at' => $timeline['arrived_in_jamaica_at'] ?? null,
            'ready_for_pickup_at' => $timeline['ready_for_pickup_at'] ?? null,
            'picked_up_at' => $timeline['picked_up_at'] ?? null,
            'customer_visible_notes' => $this->noteFor($status),
        ]);
        $this->backdate($package, $orderedAt->addHours(2));

        $this->seedHistory($package, $status, $timeline, $orderedAt);

        return $package;
    }

    private function preAlert(
        User $customer,
        string $merchant,
        Carrier $carrier,
        string $item,
        float $value,
        PreAlertStatus $status,
        int $daysAgo,
        int $expectedInDays = 4,
        ?string $tracking = null,
        ?string $adminNotes = null,
    ): PreAlert {
        $createdAt = now()->subDays($daysAgo)->subHours($this->random->getInt(1, 10));

        $preAlert = PreAlert::query()->create([
            'user_id' => $customer->id,
            'merchant_name' => $merchant,
            'order_number' => strtoupper(substr(preg_replace('/\W/', '', $merchant), 0, 2)).'-'.$this->random->getInt(1000000, 9999999),
            'tracking_number' => $tracking ?? $this->trackingNumber($carrier),
            'carrier' => $carrier->value,
            'expected_delivery_date' => now()->addDays($expectedInDays)->toDateString(),
            'item_description' => $item,
            'declared_value' => $value,
            'status' => $status,
            'admin_notes' => $adminNotes,
        ]);
        $this->backdate($preAlert, $createdAt);

        return $preAlert;
    }

    /**
     * @param  array<string, CarbonImmutable>  $timeline
     */
    private function seedHistory(Package $package, PackageStatus $final, array $timeline, CarbonImmutable $orderedAt): void
    {
        $steps = [
            [PackageStatus::ReceivedAtFloridaWarehouse, $timeline['received_at_warehouse_at'] ?? null],
            [PackageStatus::Processing, isset($timeline['received_at_warehouse_at']) && $this->stageOf($final) >= 2 ? $timeline['received_at_warehouse_at']->addHours(6) : null],
            [PackageStatus::InTransitToJamaica, $timeline['shipped_to_jamaica_at'] ?? null],
            [PackageStatus::ArrivedInJamaica, $timeline['arrived_in_jamaica_at'] ?? null],
            [PackageStatus::CustomsProcessing, isset($timeline['arrived_in_jamaica_at']) && $this->stageOf($final) >= 5 ? $timeline['arrived_in_jamaica_at']->addHours(5) : null],
            [PackageStatus::ReadyForPickup, $timeline['ready_for_pickup_at'] ?? null],
            [PackageStatus::PickedUp, $timeline['picked_up_at'] ?? null],
        ];

        $previous = PackageStatus::AwaitingArrival;
        $this->history($package, null, PackageStatus::AwaitingArrival, $orderedAt->addHours(2));

        foreach ($steps as [$status, $at]) {
            if ($at === null) {
                continue;
            }

            $this->history($package, $previous, $status, $at);
            $previous = $status;
        }

        if ($final === PackageStatus::OnHold) {
            $this->history($package, $previous, PackageStatus::OnHold, now()->subHours(20), 'Customs asked for a clearer invoice.');
        }
    }

    private function history(Package $package, ?PackageStatus $old, PackageStatus $new, CarbonImmutable $at, ?string $notes = null): void
    {
        $row = PackageStatusHistory::query()->create([
            'package_id' => $package->id,
            'old_status' => $old?->value,
            'new_status' => $new->value,
            'changed_by' => $new === PackageStatus::AwaitingArrival ? null : $this->team['staff']->id,
            'notes' => $notes,
        ]);
        $this->backdate($row, $at);
    }

    /**
     * Timestamps for each stage the package has reached, spaced like a real
     * Miami to Kingston air run, and never in the future.
     *
     * @return array<string, CarbonImmutable>
     */
    private function timeline(CarbonImmutable $orderedAt, int $stage, PackageStatus $status): array
    {
        $cap = fn (CarbonImmutable $at): CarbonImmutable => $at->isFuture() ? now()->subMinutes(30) : $at;
        $t = [];

        if ($stage >= 1) {
            $t['received_at_warehouse_at'] = $cap($orderedAt->addDays(2)->setTime(10, 15));
        }
        if ($stage >= 3) {
            $t['shipped_to_jamaica_at'] = $cap($orderedAt->addDays(3)->setTime(18, 40));
        }
        if ($stage >= 4) {
            $t['arrived_in_jamaica_at'] = $cap($orderedAt->addDays(5)->setTime(7, 5));
        }
        if ($stage >= 6) {
            $t['ready_for_pickup_at'] = $cap($orderedAt->addDays(6)->setTime(13, 30));
        }
        if ($status === PackageStatus::PickedUp) {
            $t['picked_up_at'] = $cap($orderedAt->addDays(8)->setTime(15, 20));
        }

        return $t;
    }

    /**
     * How long ago a package in this status was ordered, so its dates match
     * where it is in the journey.
     */
    private function daysAgoFor(PackageStatus $status): int
    {
        return match ($status) {
            PackageStatus::AwaitingArrival => $this->random->getInt(0, 2),
            PackageStatus::ReceivedAtFloridaWarehouse, PackageStatus::Processing => $this->random->getInt(2, 3),
            PackageStatus::InTransitToJamaica => $this->random->getInt(4, 5),
            PackageStatus::ArrivedInJamaica, PackageStatus::CustomsProcessing, PackageStatus::OnHold => $this->random->getInt(5, 7),
            PackageStatus::ReadyForPickup => $this->random->getInt(7, 14),
            default => $this->random->getInt(10, 170),
        };
    }

    private function stageOf(PackageStatus $status): int
    {
        return match ($status) {
            PackageStatus::AwaitingArrival => 0,
            PackageStatus::ReceivedAtFloridaWarehouse => 1,
            PackageStatus::Processing => 2,
            PackageStatus::InTransitToJamaica => 3,
            PackageStatus::ArrivedInJamaica => 4,
            PackageStatus::CustomsProcessing, PackageStatus::OnHold => 5,
            PackageStatus::ReadyForPickup => 6,
            PackageStatus::PickedUp => 7,
            default => 0,
        };
    }

    private function noteFor(PackageStatus $status): string
    {
        return match ($status) {
            PackageStatus::ReadyForPickup => 'Ready at our Half Way Tree counter. Bring ID and your customer number.',
            PackageStatus::PickedUp => 'Collected. Thanks for shipping with Blue Mahoe.',
            PackageStatus::OnHold => 'On hold with customs. We will message you with next steps.',
            PackageStatus::CustomsProcessing => 'Clearing customs in Kingston.',
            PackageStatus::ArrivedInJamaica => 'Landed in Kingston.',
            PackageStatus::InTransitToJamaica => 'On the flight to Kingston.',
            PackageStatus::AwaitingArrival => 'Waiting for it to reach our Miami warehouse.',
            default => 'At our Miami warehouse.',
        };
    }

    private function user(string $name, string $email, string $role, CarbonImmutable $createdAt, bool $demoLogin = false): User
    {
        $user = (new User)->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($demoLogin ? (string) config('courieros.demo.password') : str()->random(32)),
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => $createdAt,
        ]);
        $user->save();
        $this->backdate($user, $createdAt);

        return $user;
    }

    private function trackingNumber(Carrier $carrier): string
    {
        $n = fn (int $len): string => implode('', array_map(fn () => (string) $this->random->getInt(0, 9), range(1, $len)));

        return match ($carrier) {
            Carrier::AmazonLogistics => 'TBA'.$n(12),
            Carrier::Ups => '1Z'.strtoupper(bin2hex($this->random->getBytes(3))).$n(10),
            Carrier::Usps => '9400'.$n(18),
            Carrier::Fedex => $n(12),
            Carrier::Dhl => 'JD'.$n(16),
            default => 'BM'.$n(10),
        };
    }

    private function phone(): string
    {
        return '+1 (876) 555-'.str_pad((string) $this->random->getInt(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @template T
     *
     * @param  list<T>  $items
     * @return T
     */
    private function pick(array $items): mixed
    {
        return $items[$this->random->getInt(0, count($items) - 1)];
    }

    private function backdate(Model $model, CarbonImmutable $at): void
    {
        $model->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();
    }
}
