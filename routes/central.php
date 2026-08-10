<?php

use App\Http\Controllers\Central\BillingController;
use App\Http\Controllers\Central\MarketingController;
use App\Http\Controllers\Central\PlatformAdmin\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Central\PlatformAdmin\SupportController as PlatformSupportController;
use App\Http\Controllers\Central\PlatformAdmin\TenantController as PlatformTenantController;
use App\Http\Controllers\Central\TenantSignupController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central (platform) routes
|--------------------------------------------------------------------------
|
| These routes serve the CourierOS platform itself: marketing, tenant
| signup, and platform billing. They run on the central domain (apex /
| app / www) and are NOT scoped to a tenant.
|
*/

Route::name('central.')->group(function () {
    // Stripe posts to a fixed URL on the central domain and verifies by
    // signature, so it stays outside the `central` guard.
    Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
        ->name('cashier.webhook');

    // Everything below is the CourierOS *platform*, not a tenant's site.
    // Without `central` these routes also answer on every tenant subdomain,
    // leaking CourierOS branding and pricing into a white-label customer's
    // own domain.
    Route::middleware('central')->group(function () {
        // Marketing pages for the platform itself. `/` stays on
        // PublicPageController because it has to fall through to a tenant's
        // own home page; these two never do.
        Route::get('product', [MarketingController::class, 'product'])->name('product');
        Route::get('pricing', [MarketingController::class, 'pricing'])->name('pricing');
        Route::get('jamaica', [MarketingController::class, 'jamaica'])->name('jamaica');

        Route::get('signup', [TenantSignupController::class, 'show'])->name('signup.show');
        Route::post('signup', [TenantSignupController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('signup.store');

        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('checkout/{tenant}', [BillingController::class, 'checkout'])->name('checkout');
            Route::get('success/{tenant}', [BillingController::class, 'success'])->name('success');
            Route::get('cancelled', [BillingController::class, 'cancel'])->name('cancel');
        });
    });

    Route::middleware(['auth', 'platform'])
        ->prefix('platform')
        ->name('platform.')
        ->group(function () {
            Route::get('/', PlatformDashboardController::class)->name('dashboard');
            Route::get('tenants', [PlatformTenantController::class, 'index'])->name('tenants.index');
            Route::patch('tenants/{tenant}', [PlatformTenantController::class, 'update'])->name('tenants.update');

            Route::get('support', [PlatformSupportController::class, 'index'])->name('support.index');
            Route::post('support', [PlatformSupportController::class, 'store'])
                ->middleware('throttle:30,1')
                ->name('support.store');
            Route::get('support/{thread}', [PlatformSupportController::class, 'show'])->name('support.show');
            Route::post('support/{thread}/messages', [PlatformSupportController::class, 'storeMessage'])
                ->middleware('throttle:30,1')
                ->name('support.messages.store');
            Route::patch('support/{thread}', [PlatformSupportController::class, 'update'])->name('support.update');
        });
});
