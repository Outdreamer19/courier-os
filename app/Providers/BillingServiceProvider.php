<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // The courier business (tenant) is the billable party for the
        // CourierOS platform subscription, so Cashier bills the Tenant model.
        Cashier::useCustomerModel(Tenant::class);
    }
}
