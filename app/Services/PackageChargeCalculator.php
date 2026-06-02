<?php

namespace App\Services;

use App\Models\ShippingRate;

/**
 * Calculates package shipping charges from the active rate table.
 */
class PackageChargeCalculator
{
    public function calculate(?float $weightLbs): float
    {
        if ($weightLbs === null || $weightLbs <= 0) {
            return 0;
        }

        $rate = ShippingRate::forWeight($weightLbs);

        if (! $rate) {
            $perLb = (float) config('shipdjm.default_rate_per_lb', 500);

            return round(max($perLb, $weightLbs * $perLb), 2);
        }

        $charge = $weightLbs * (float) $rate->rate_per_lb;
        $charge = max($charge, (float) $rate->minimum_charge);

        if ($rate->handling_fee) {
            $charge += (float) $rate->handling_fee;
        }

        return round($charge, 2);
    }
}
