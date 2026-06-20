<?php

use App\Http\Controllers\Central\BillingController;
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
    Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
        ->name('cashier.webhook');

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
