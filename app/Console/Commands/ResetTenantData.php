<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AuthorisedPickupPerson;
use App\Models\ContactMessage;
use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\PackageStatusHistory;
use App\Models\PreAlert;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Wipes a tenant's *operational* data back to a clean, production-ready
 * state while preserving the things a courier has configured: the tenant
 * record itself, its warehouse address(es), shipping rates, branding, and
 * its staff/admin/owner accounts.
 *
 * Intended for the "demo now, real data later" workflow: seed a tenant with
 * showcase data so the dashboards look alive during client UAT, then run
 * this immediately before they start entering real shipments.
 *
 * Deleted:  packages, package status histories, pre-alerts, activity logs,
 *           contact messages, authorised pickup people, customer profiles,
 *           and all users with the `customer` role.
 * Retained: tenant, warehouse addresses, shipping rates, owner/admin/staff.
 */
class ResetTenantData extends Command
{
    protected $signature = 'tenant:reset
                            {subdomain : The tenant subdomain, e.g. "today"}
                            {--owner-email= : Replace the owner account email with a real, reachable address}
                            {--owner-name= : Replace the owner account display name}
                            {--keep-customers : Keep customer accounts and profiles, drop only shipment data}
                            {--force : Skip the confirmation prompt (required in production)}';

    protected $description = 'Reset a tenant to a clean slate: remove demo/operational data, keep configuration and staff.';

    public function handle(TenantManager $tenants): int
    {
        $subdomain = (string) $this->argument('subdomain');

        $tenant = Tenant::query()->where('subdomain', $subdomain)->first();

        if (! $tenant) {
            $this->components->error("No tenant found with subdomain [{$subdomain}].");

            return self::FAILURE;
        }

        $this->components->info("Tenant: {$tenant->name} ({$tenant->subdomain})");

        if (! $this->confirmToProceed($tenant)) {
            $this->components->warn('Aborted. Nothing was deleted.');

            return self::FAILURE;
        }

        // Bind the tenant so every BelongsToTenant global scope filters to it.
        // Without this, the deletes below would run across ALL tenants.
        $tenants->forget();
        $tenants->set($tenant);

        try {
            $deleted = DB::transaction(fn () => $this->purge());
        } finally {
            $tenants->forget();
        }

        foreach ($deleted as $label => $count) {
            $this->components->twoColumnDetail($label, (string) $count);
        }

        $this->updateOwner($tenants, $tenant);

        $this->newLine();
        $this->components->info('Tenant reset complete. Configuration, rates, warehouse and staff were preserved.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    private function purge(): array
    {
        // Order matters: children before parents, so FK constraints hold.
        $deleted = [
            'Package status histories' => PackageStatusHistory::query()->delete(),
            'Packages' => Package::query()->delete(),
            'Pre-alerts' => PreAlert::query()->delete(),
            'Activity logs' => ActivityLog::query()->delete(),
            'Contact messages' => ContactMessage::query()->delete(),
        ];

        if ($this->option('keep-customers')) {
            return $deleted;
        }

        $deleted['Authorised pickup people'] = AuthorisedPickupPerson::query()->delete();
        $deleted['Customer profiles'] = CustomerProfile::query()->delete();
        $deleted['Customer accounts'] = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->delete();

        return $deleted;
    }

    private function updateOwner(TenantManager $tenants, Tenant $tenant): void
    {
        $email = $this->option('owner-email');
        $name = $this->option('owner-name');

        if (! $email && ! $name) {
            return;
        }

        $tenants->set($tenant);

        $owner = User::query()->where('role', User::ROLE_OWNER)->first();

        $tenants->forget();

        if (! $owner) {
            $this->components->warn('No owner account found for this tenant; owner details were not changed.');

            return;
        }

        $owner->forceFill(array_filter([
            'email' => $email,
            'name' => $name,
        ]))->save();

        $this->components->twoColumnDetail('Owner account', $owner->email);
    }

    private function confirmToProceed(Tenant $tenant): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if (app()->environment('production')) {
            $this->components->warn(
                'Running in production. Re-run with --force once you are certain.'
            );

            return false;
        }

        return $this->confirm(
            "This permanently deletes {$tenant->name}'s packages, pre-alerts, customers and logs. Continue?",
            false
        );
    }
}
