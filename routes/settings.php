<?php

use App\Http\Controllers\Settings\BrandingController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');
});

// Branding writes to the resolved tenant, so unlike the personal settings
// above it is meaningless — and unscoped — on the central domain.
Route::middleware(['auth', 'tenant', 'admin.permission:manage_system_settings'])->group(function () {
    Route::get('settings/branding', [BrandingController::class, 'edit'])->name('branding.edit');
    Route::patch('settings/branding', [BrandingController::class, 'update'])->name('branding.update');
    Route::post('settings/branding/logo', [BrandingController::class, 'uploadLogo'])->name('branding.logo.store');
    Route::delete('settings/branding/logo', [BrandingController::class, 'destroyLogo'])->name('branding.logo.destroy');
});
