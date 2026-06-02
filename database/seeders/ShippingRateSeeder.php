<?php

namespace Database\Seeders;

use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingRateSeeder extends Seeder
{
    public function run(): void
    {
        if (ShippingRate::query()->exists()) {
            return;
        }

        ShippingRate::create([
            'name' => 'Standard Air Shipping (placeholder)',
            'method' => 'standard',
            'currency' => config('shipdjm.currency', 'JMD'),
            'rate_per_lb' => (float) config('shipdjm.default_rate_per_lb', 500),
            'minimum_charge' => (float) config('shipdjm.default_rate_per_lb', 500),
            'handling_fee' => null,
            'min_weight_lbs' => null,
            'max_weight_lbs' => null,
            'is_active' => true,
        ]);
    }
}
