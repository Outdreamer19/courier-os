<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| These only run if the scheduler is running on the server:
|   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
| On Forge that is the "Scheduler" toggle on the site.
|
*/

// InvoiceFeed pushes an invoice.paid webhook, but webhooks do get dropped —
// during a deploy, on a network blip, or if the endpoint 500s. This is the
// reconciliation backstop, so a package that has actually been paid never sits
// showing "unpaid" to the customer indefinitely.
//
// Runs platform-wide on purpose: no tenant is bound in CLI context, so the
// BelongsToTenant global scope is a no-op and every tenant is reconciled.
Schedule::command('shipdjm:sync-invoicefeed-payments')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// The public demo courier (demo.courieros.co) is wiped and reseeded every
// night so each day's visitors start from the same clean showcase.
if (config('courieros.demo.enabled', true)) {
    Schedule::command('demo:reset')
        ->dailyAt((string) config('courieros.demo.reset_time', '04:00'))
        ->timezone((string) config('courieros.demo.reset_timezone', 'America/Jamaica'))
        ->withoutOverlapping();
}

// Nightly database backup to Cloudflare R2 (see config/backup.php). Old
// backups are pruned first, and a morning health check emails the owner if
// the newest backup is missing or more than a day old. Times are UTC.
Schedule::command('backup:clean')->dailyAt('01:30')->withoutOverlapping();
Schedule::command('backup:run --only-db')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('backup:monitor')->dailyAt('08:00');
