<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PackageBillingController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\PreAlertController as AdminPreAlertController;
use App\Http\Controllers\Admin\PreAlertInvoiceController as AdminPreAlertInvoiceController;
use App\Http\Controllers\Admin\ReportsController as AdminReportsController;
use App\Http\Controllers\Admin\ShippingRateController as AdminShippingRateController;
use App\Http\Controllers\Admin\WarehouseAddressController as AdminWarehouseAddressController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Customer\AuthorisedPickupPersonController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\PackageController;
use App\Http\Controllers\Customer\PreAlertController;
use App\Http\Controllers\Customer\PreAlertInvoiceController;
use App\Http\Controllers\Customer\ShippingAddressController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\RateCalculatorController;
use App\Http\Controllers\Webhooks\InvoiceFeedWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public marketing site
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('about', [PublicPageController::class, 'about'])->name('about');
Route::get('rates', [PublicPageController::class, 'rates'])->name('rates');
Route::post('rates/calculate', RateCalculatorController::class)
    ->middleware('throttle:60,1')
    ->name('rates.calculate');

Route::get('contact', [ContactController::class, 'show'])->name('contact');
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('contact.store');

Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('terms', [PublicPageController::class, 'terms'])->name('terms');
    Route::get('privacy', [PublicPageController::class, 'privacy'])->name('privacy');
    Route::get('shipping', [PublicPageController::class, 'shipping'])->name('shipping');
    Route::get('refund', [PublicPageController::class, 'refund'])->name('refund');
    Route::get('restricted-items', [PublicPageController::class, 'restrictedItems'])->name('restricted');
});

// Backward-compatible aliases for direct links and SEO-friendly slugs.
Route::redirect('terms', '/legal/terms', 301);
Route::redirect('privacy', '/legal/privacy', 301);
Route::redirect('shipping-policy', '/legal/shipping', 301);
Route::redirect('refund-policy', '/legal/refund', 301);
Route::redirect('restricted-items', '/legal/restricted-items', 301);

Route::post('webhooks/invoicefeed', InvoiceFeedWebhookController::class)
    ->name('webhooks.invoicefeed');

/*
|--------------------------------------------------------------------------
| Authenticated portal
|--------------------------------------------------------------------------
| The /dashboard route is the post-login landing page for every role. Admins
| are bounced to /admin and customers see the customer dashboard.
*/
Route::middleware(['auth', 'verified', 'tenant.member', 'tenant.subscribed'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('portal')
        ->name('portal.')
        ->middleware('role:customer')
        ->group(function () {
            Route::get('profile', [CustomerProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('profile', [CustomerProfileController::class, 'update'])->name('profile.update');
            Route::post('authorised-pickup-people', [AuthorisedPickupPersonController::class, 'store'])
                ->name('authorised-pickup-people.store');
            Route::delete('authorised-pickup-people/{authorised_pickup_person}', [AuthorisedPickupPersonController::class, 'destroy'])
                ->name('authorised-pickup-people.destroy');

            Route::get('shipping-address', ShippingAddressController::class)->name('shipping-address');

            Route::resource('pre-alerts', PreAlertController::class)->except(['destroy']);
            Route::post('pre-alerts/{pre_alert}/cancel', [PreAlertController::class, 'cancel'])
                ->name('pre-alerts.cancel');
            Route::get('pre-alerts/{pre_alert}/invoice', PreAlertInvoiceController::class)
                ->name('pre-alerts.invoice');

            Route::resource('packages', PackageController::class)->only(['index', 'show']);
        });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:owner,admin,staff')
        ->group(function () {
            Route::get('/', AdminDashboardController::class)->name('dashboard');

            Route::resource('customers', AdminCustomerController::class)
                ->except(['destroy']);

            Route::resource('pre-alerts', AdminPreAlertController::class)->only(['index', 'show', 'update']);
            Route::get('pre-alerts/{pre_alert}/invoice', AdminPreAlertInvoiceController::class)
                ->name('pre-alerts.invoice');

            Route::resource('packages', AdminPackageController::class)
                ->except(['show', 'destroy']);

            Route::post('packages/{package}/billing/generate-invoice', [PackageBillingController::class, 'generateInvoice'])
                ->middleware('admin.permission:manage_billing')
                ->name('packages.billing.generate-invoice');
            Route::post('packages/{package}/billing/send-invoice', [PackageBillingController::class, 'sendInvoice'])
                ->middleware('admin.permission:manage_billing')
                ->name('packages.billing.send-invoice');
            Route::post('packages/{package}/billing/payment-link', [PackageBillingController::class, 'createPaymentLink'])
                ->middleware('admin.permission:manage_billing')
                ->name('packages.billing.payment-link');
            Route::post('packages/{package}/billing/sync-payment', [PackageBillingController::class, 'syncPaymentStatus'])
                ->middleware('admin.permission:manage_billing')
                ->name('packages.billing.sync-payment');

            Route::resource('contact-messages', AdminContactMessageController::class)
                ->only(['index', 'show', 'update', 'destroy'])
                ->parameters(['contact-messages' => 'contact_message'])
                ->middleware('admin.permission:manage_contact_messages');

            Route::resource('shipping-rates', AdminShippingRateController::class)
                ->except(['show', 'destroy'])
                ->middleware('admin.permission:manage_system_settings');

            Route::get('warehouse', [AdminWarehouseAddressController::class, 'index'])
                ->middleware('admin.permission:manage_system_settings')
                ->name('warehouse.index');
            Route::get('warehouse/{warehouse}/edit', [AdminWarehouseAddressController::class, 'edit'])
                ->middleware('admin.permission:manage_system_settings')
                ->name('warehouse.edit');
            Route::put('warehouse/{warehouse}', [AdminWarehouseAddressController::class, 'update'])
                ->middleware('admin.permission:manage_system_settings')
                ->name('warehouse.update');

            Route::resource('admin-users', AdminUserController::class)
                ->except(['show', 'destroy'])
                ->parameters(['admin-users' => 'admin_user'])
                ->middleware('admin.permission:manage_admins');

            Route::get('activity-logs', [ActivityLogController::class, 'index'])
                ->middleware('admin.permission:view_activity_logs')
                ->name('activity-logs.index');

            Route::get('reports', [AdminReportsController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [AdminReportsController::class, 'export'])->name('reports.export');
        });
});

require __DIR__.'/settings.php';
