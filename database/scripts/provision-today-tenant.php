<?php

/**
 * Provisions the "Today Shipping & Logistics" tenant on a live server.
 *
 * Deliberately minimal: it creates ONLY this tenant, its warehouse, its
 * shipping rate and one owner account. It does not touch CourierOsSeeder or
 * TodayShippingDataSeeder, both of which create extra demo tenants and
 * accounts with the password "password" — unacceptable on a public server.
 *
 * Idempotent: safe to run more than once. Re-running updates the existing
 * records rather than duplicating them, and only sets the owner's password
 * when TODAY_OWNER_PASSWORD is supplied.
 *
 * Usage (on the server, from the site root):
 *
 *   read -rs -p "Owner password: " TODAY_OWNER_PASSWORD; echo
 *   export TODAY_OWNER_PASSWORD
 *   php artisan tinker database/scripts/provision-today-tenant.php
 *   unset TODAY_OWNER_PASSWORD
 *
 * Reading the password into a variable keeps it out of your shell history.
 */

use App\Models\ShippingRate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WarehouseAddress;
use App\Support\Tenancy\TenantManager;
use Illuminate\Support\Facades\Hash;

$ownerEmail = 'admin@todayshippingja.com';
$ownerName = 'Today Shipping Admin';
$password = getenv('TODAY_OWNER_PASSWORD') ?: null;

$manager = app(TenantManager::class);
$manager->forget();

$tenant = Tenant::query()->updateOrCreate(
    ['subdomain' => 'today'],
    [
        'name' => 'Today Shipping & Logistics',
        'status' => Tenant::STATUS_ACTIVE,
        'currency' => 'JMD',
        'customer_reference_prefix' => 'TSL',
        'package_reference_prefix' => 'PKG',
        'brand_primary_color' => '#D0202E',
    ],
);

echo "Tenant #{$tenant->id} {$tenant->name} ({$tenant->status})\n";

// Bind the tenant so BelongsToTenant stamps tenant_id on everything below.
$manager->set($tenant);

$owner = User::query()->firstOrNew([
    'email' => $ownerEmail,
    'tenant_id' => $tenant->id,
]);

$owner->fill([
    'name' => $ownerName,
    'role' => User::ROLE_OWNER,
    'status' => User::STATUS_ACTIVE,
    'tenant_id' => $tenant->id,
]);

// Pre-verify the owner so they are not blocked by the `verified` middleware
// before mail delivery has been confirmed working.
$owner->email_verified_at ??= now();

if ($password) {
    $owner->password = Hash::make($password);
} elseif (! $owner->exists) {
    $manager->forget();
    throw new RuntimeException(
        'TODAY_OWNER_PASSWORD is not set and the owner does not exist yet. '
        .'Export a password and re-run.'
    );
}

$owner->save();

echo "Owner #{$owner->id} {$owner->email}"
    .($password ? ' (password set)' : ' (password unchanged)')."\n";

$warehouse = WarehouseAddress::query()->updateOrCreate(
    ['name' => 'Today Shipping Miami Warehouse', 'tenant_id' => $tenant->id],
    [
        'address_line_1' => '1234 Logistics Way',
        'address_line_2' => 'Suite TSL',
        'city' => 'Miami',
        'state' => 'FL',
        'zip' => '33101',
        'phone' => '+1 (305) 555-0100',
        'instructions' => 'Use your customer reference as the suite number.',
        'is_active' => true,
    ],
);

echo "Warehouse #{$warehouse->id} {$warehouse->name}\n";
echo "  !! Placeholder address — replace via Admin > Warehouse before the client tests.\n";

$rate = ShippingRate::query()->updateOrCreate(
    ['name' => 'Standard Air', 'tenant_id' => $tenant->id],
    [
        'method' => 'standard',
        'currency' => 'JMD',
        'rate_per_lb' => 750,
        'minimum_charge' => 750,
        'is_active' => true,
    ],
);

echo "Rate #{$rate->id} {$rate->name} @ {$rate->rate_per_lb} {$rate->currency}/lb\n";

$manager->forget();

echo "\nDone. https://today.courieros.co should now resolve to the tenant.\n";
