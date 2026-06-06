<?php

namespace App\Console\Commands;

use App\Enums\BillingStatus;
use App\Exceptions\InvoiceFeedException;
use App\Models\Package;
use App\Services\InvoiceFeed\InvoiceFeedBillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncInvoiceFeedPayments extends Command
{
    protected $signature = 'shipdjm:sync-invoicefeed-payments';

    protected $description = 'Sync payment status from InvoiceFeed for outstanding package invoices';

    public function handle(InvoiceFeedBillingService $billing): int
    {
        $packages = Package::query()
            ->whereNotNull('invoicefeed_invoice_id')
            ->where('billing_status', '!=', BillingStatus::Paid->value)
            ->get();

        if ($packages->isEmpty()) {
            $this->info('No packages to sync.');

            return self::SUCCESS;
        }

        $synced = 0;
        $paid = 0;
        $failed = 0;

        foreach ($packages as $package) {
            try {
                $updated = $billing->syncPaymentStatus($package);
                $synced++;

                if ($updated->billing_status === BillingStatus::Paid) {
                    $paid++;
                    $this->line("Paid: {$updated->package_reference}");
                }
            } catch (InvoiceFeedException $exception) {
                $failed++;
                Log::warning('InvoiceFeed payment sync failed for package', [
                    'package_id' => $package->id,
                    'message' => $exception->getMessage(),
                    'context' => $exception->context(),
                ]);
                $this->warn("Failed: {$package->package_reference} — {$exception->userMessage()}");
            }
        }

        $this->info("Synced {$synced} package(s). {$paid} marked paid. {$failed} failed.");

        return self::SUCCESS;
    }
}
