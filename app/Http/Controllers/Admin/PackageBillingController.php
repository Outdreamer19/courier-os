<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BillingStatus;
use App\Exceptions\InvoiceFeedException;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\InvoiceFeed\InvoiceFeedBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PackageBillingController extends Controller
{
    public function generateInvoice(Package $package, InvoiceFeedBillingService $billing): RedirectResponse
    {
        $this->authorize('update', $package);

        return $this->runBillingAction(
            $package,
            fn () => $billing->generateInvoice($package),
            'Invoice generated.',
        );
    }

    public function sendInvoice(Package $package, InvoiceFeedBillingService $billing): RedirectResponse
    {
        $this->authorize('update', $package);

        return $this->runBillingAction(
            $package,
            fn () => $billing->sendInvoice($package),
            'Invoice sent to customer.',
        );
    }

    public function createPaymentLink(Package $package, InvoiceFeedBillingService $billing): RedirectResponse
    {
        $this->authorize('update', $package);

        return $this->runBillingAction(
            $package,
            fn () => $billing->createPaymentLink($package),
            'Payment link updated.',
        );
    }

    public function syncPaymentStatus(Package $package, InvoiceFeedBillingService $billing): RedirectResponse
    {
        $this->authorize('update', $package);

        return $this->runBillingAction(
            $package,
            fn () => $billing->syncPaymentStatus($package),
            'Payment status synced.',
        );
    }

    /**
     * @param  callable(): Package  $action
     */
    private function runBillingAction(Package $package, callable $action, string $successMessage): RedirectResponse
    {
        try {
            $action();

            Inertia::flash('toast', ['type' => 'success', 'message' => $successMessage]);
        } catch (InvoiceFeedException $exception) {
            if (! $package->invoicefeed_invoice_id) {
                $package->update(['billing_status' => BillingStatus::Failed]);
            }

            Log::warning('InvoiceFeed billing action failed', [
                'package_id' => $package->id,
                'message' => $exception->getMessage(),
                'context' => $exception->context(),
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $exception->userMessage(),
            ]);
        }

        return to_route('admin.packages.edit', ['package' => $package]);
    }
}
