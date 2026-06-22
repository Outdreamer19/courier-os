<?php

namespace Database\Seeders;

use App\Enums\PackageStatus;
use App\Enums\PaymentStatus;
use App\Enums\PreAlertStatus;
use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\PreAlert;
use App\Models\ShippingRate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WarehouseAddress;
use App\Support\Tenancy\TenantManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a realistic multi-tenant CourierOS environment:
 *  - one platform owner (CourierOS operator)
 *  - two demo courier tenants, each with an owner, a customer, warehouse,
 *    rate, pre-alerts and packages — all correctly tenant-scoped.
 *
 * All accounts use the password "password" for easy local testing.
 */
class CourierOsSeeder extends Seeder
{
    public function run(): void
    {
        $manager = app(TenantManager::class);
        $manager->forget();

        // Platform owner — central context, no tenant.
        User::query()->updateOrCreate(
            ['email' => 'platform@courieros.co'],
            [
                'name' => 'CourierOS Platform Owner',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PLATFORM_OWNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'tenant_id' => null,
            ],
        );

        $this->seedTenant($manager, [
            'name' => "Ship'd JM",
            'subdomain' => 'shipd',
            'currency' => 'JMD',
            'prefix' => 'SJM',
            'owner_email' => 'owner@shipd.test',
            'customer_email' => 'customer@shipd.test',
            'customer_name' => 'Marcia Brown',
            'warehouse' => "Ship'd JM Florida Warehouse",
        ]);

        $this->seedTenant($manager, [
            'name' => 'Island Express',
            'subdomain' => 'island',
            'currency' => 'USD',
            'prefix' => 'ISL',
            'owner_email' => 'owner@island.test',
            'customer_email' => 'customer@island.test',
            'customer_name' => 'Damion Clarke',
            'warehouse' => 'Island Express Miami Hub',
        ]);

        $manager->forget();
    }

    /**
     * @param  array<string, string>  $config
     */
    private function seedTenant(TenantManager $manager, array $config): void
    {
        $manager->forget();

        $tenant = Tenant::query()->updateOrCreate(
            ['subdomain' => $config['subdomain']],
            [
                'name' => $config['name'],
                'status' => Tenant::STATUS_ACTIVE,
                'currency' => $config['currency'],
                'customer_reference_prefix' => $config['prefix'],
                'package_reference_prefix' => 'PKG',
                'whatsapp_number' => '+1 (876) 555-0000',
            ],
        );

        // Bind so all subsequent creates are tenant-scoped.
        $manager->set($tenant);

        User::query()->updateOrCreate(
            ['email' => $config['owner_email'], 'tenant_id' => $tenant->id],
            [
                'name' => $config['name'].' Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OWNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
        );

        WarehouseAddress::query()->updateOrCreate(
            ['name' => $config['warehouse'], 'tenant_id' => $tenant->id],
            [
                'address_line_1' => '1234 Logistics Way',
                'address_line_2' => 'Suite '.$config['prefix'],
                'city' => 'Miami',
                'state' => 'FL',
                'zip' => '33101',
                'phone' => '+1 (305) 555-0100',
                'instructions' => 'Use your customer reference as the suite number.',
                'is_active' => true,
            ],
        );

        ShippingRate::query()->updateOrCreate(
            ['name' => 'Standard Air', 'tenant_id' => $tenant->id],
            [
                'method' => 'standard',
                'currency' => $config['currency'],
                'rate_per_lb' => $config['currency'] === 'JMD' ? 500 : 4,
                'minimum_charge' => $config['currency'] === 'JMD' ? 500 : 4,
                'is_active' => true,
            ],
        );

        $customer = User::query()->updateOrCreate(
            ['email' => $config['customer_email'], 'tenant_id' => $tenant->id],
            [
                'name' => $config['customer_name'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
        );

        CustomerProfile::query()->updateOrCreate(
            ['user_id' => $customer->id],
            [
                'customer_reference' => $config['prefix'].'-000001',
                'trn' => '123456789',
                'date_of_birth' => '1990-05-12',
                'phone' => '+1 (876) 555-0150',
                'whatsapp_number' => '+1 (876) 555-0150',
                'jamaica_address' => '15 Constant Spring Road',
                'parish' => 'Kingston',
            ],
        );

        $preAlert = PreAlert::query()->updateOrCreate(
            ['tracking_number' => $config['prefix'].'-TRK-001', 'tenant_id' => $tenant->id],
            [
                'user_id' => $customer->id,
                'merchant_name' => 'Amazon',
                'order_number' => '111-2233445',
                'carrier' => 'amazon_logistics',
                'expected_delivery_date' => now()->addDays(4),
                'item_description' => 'Electronics — headphones and charger',
                'declared_value' => 75.00,
                'status' => PreAlertStatus::MatchedToPackage,
            ],
        );

        Package::query()->updateOrCreate(
            ['package_reference' => 'PKG-'.$config['prefix'].'-001', 'tenant_id' => $tenant->id],
            [
                'user_id' => $customer->id,
                'pre_alert_id' => $preAlert->id,
                'tracking_number' => $config['prefix'].'-TRK-001',
                'merchant_name' => 'Amazon',
                'carrier' => 'amazon_logistics',
                'weight_lbs' => 3.5,
                'declared_value' => 75.00,
                'amount_due' => $config['currency'] === 'JMD' ? 1750 : 14,
                'payment_status' => PaymentStatus::Unpaid,
                'status' => PackageStatus::ReadyForPickup,
                'received_at_warehouse_at' => now()->subWeeks(2),
                'shipped_to_jamaica_at' => now()->subWeek(),
                'arrived_in_jamaica_at' => now()->subDays(3),
                'ready_for_pickup_at' => now()->subDay(),
                'customer_visible_notes' => 'Ready for pickup. Bring ID and your reference.',
            ],
        );

        Package::query()->updateOrCreate(
            ['package_reference' => 'PKG-'.$config['prefix'].'-002', 'tenant_id' => $tenant->id],
            [
                'user_id' => $customer->id,
                'tracking_number' => $config['prefix'].'-TRK-002',
                'merchant_name' => 'SHEIN',
                'carrier' => 'usps',
                'weight_lbs' => 1.2,
                'declared_value' => 40.00,
                'amount_due' => $config['currency'] === 'JMD' ? 600 : 5,
                'payment_status' => PaymentStatus::Unpaid,
                'status' => PackageStatus::InTransitToJamaica,
                'received_at_warehouse_at' => now()->subDays(4),
                'shipped_to_jamaica_at' => now()->subDays(1),
                'customer_visible_notes' => 'On its way to Jamaica.',
            ],
        );

        $manager->forget();
    }
}
