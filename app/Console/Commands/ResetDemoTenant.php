<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AuthorisedPickupPerson;
use App\Models\ContactMessage;
use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\PackageStatusHistory;
use App\Models\PlatformSupportThread;
use App\Models\PreAlert;
use App\Models\ShippingRate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WarehouseAddress;
use App\Support\Tenancy\CentralDomain;
use App\Support\Tenancy\TenantManager;
use Database\Seeders\DemoCourierSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Wipes the public demo courier and seeds it again from scratch.
 *
 * Scheduled nightly (routes/console.php) so whatever visitors did during the
 * day disappears. Safe to run any time: it only ever touches the tenant on
 * the configured demo subdomain, and refuses to run if that tenant looks
 * like a real, paying courier.
 */
class ResetDemoTenant extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Wipe and reseed the public demo courier (demo.courieros.co).';

    public function handle(TenantManager $tenants): int
    {
        $subdomain = CentralDomain::demoSubdomain();

        if ($subdomain === null) {
            $this->components->warn('The demo is switched off (COURIEROS_DEMO_ENABLED=false). Nothing to do.');

            return self::SUCCESS;
        }

        $tenant = Tenant::query()->where('subdomain', $subdomain)->first();

        if ($tenant && ($tenant->stripe_id || $tenant->subscriptions()->exists() || filled($tenant->custom_domain))) {
            $this->components->error("The tenant on [{$subdomain}] has billing or a custom domain, so it is not the demo. Refusing to touch it.");

            return self::FAILURE;
        }

        $tenants->forget();

        DB::transaction(function () use ($tenant, $tenants): void {
            if ($tenant) {
                $this->purge($tenant, $tenants);
            }

            (new DemoCourierSeeder)->run();
        });

        $tenants->forget();

        $this->components->info('Demo courier reset: '.DemoCourierSeeder::NAME." on {$subdomain}.".config('courieros.central_domain'));

        return self::SUCCESS;
    }

    private function purge(Tenant $tenant, TenantManager $tenants): void
    {
        // Bind the tenant so every BelongsToTenant scope filters to it alone.
        $tenants->set($tenant);

        try {
            $invoices = PreAlert::query()->whereNotNull('invoice_path')->pluck('invoice_path')->all();

            PackageStatusHistory::query()->delete();
            Package::query()->delete();
            PreAlert::query()->delete();
            ActivityLog::query()->delete();
            ContactMessage::query()->delete();
            AuthorisedPickupPerson::query()->delete();
            CustomerProfile::query()->delete();
            ShippingRate::query()->delete();
            WarehouseAddress::query()->delete();
            // Support threads point at users, so they go before the users.
            PlatformSupportThread::query()->where('tenant_id', $tenant->id)->delete();
            User::query()->delete();
        } finally {
            $tenants->forget();
        }

        if ($invoices !== []) {
            Storage::disk(config('shipdjm.invoice_uploads.disk', 'local'))->delete($invoices);
        }

        if ($tenant->logo_path && $tenant->logo_path !== DemoCourierSeeder::LOGO_PATH) {
            Storage::disk('public')->delete($tenant->logo_path);
        }

        $tenant->delete();
    }
}
