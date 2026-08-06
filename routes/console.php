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
