<?php

namespace App\Support;

use App\Models\ShippingRate;
use App\Services\PackageChargeCalculator;
use App\Support\Tenancy\TenantConfig;

class ShippingRatePresenter
{
    public function __construct(private readonly PackageChargeCalculator $calculator) {}

    /**
     * @return array<string, mixed>|null
     */
    public function primaryRate(): ?array
    {
        $rate = ShippingRate::query()->active()->orderByDesc('updated_at')->first();

        return $rate ? $this->formatRate($rate) : $this->fallbackRate();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function activeTiers(): array
    {
        $rates = ShippingRate::query()
            ->active()
            ->orderByRaw('COALESCE(min_weight_lbs, 0) ASC')
            ->orderByDesc('updated_at')
            ->get();

        if ($rates->isEmpty()) {
            return [$this->fallbackRate()];
        }

        return $rates->map(fn (ShippingRate $rate) => $this->formatRate($rate))->all();
    }

    /**
     * @return array{amount: float, currency: string, rate: array<string, mixed>|null}
     */
    public function estimate(float $weightLbs): array
    {
        $rate = ShippingRate::forWeight($weightLbs);

        return [
            'amount' => $this->calculator->calculate($weightLbs),
            'currency' => $rate?->currency ?? app(TenantConfig::class)->currency(),
            'rate' => $rate ? $this->formatRate($rate) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRate(ShippingRate $rate): array
    {
        return [
            'id' => $rate->id,
            'name' => $rate->name,
            'method' => $rate->method,
            'currency' => $rate->currency,
            'rate_per_lb' => (float) $rate->rate_per_lb,
            'minimum_charge' => (float) $rate->minimum_charge,
            'handling_fee' => $rate->handling_fee !== null ? (float) $rate->handling_fee : null,
            'min_weight_lbs' => $rate->min_weight_lbs !== null ? (float) $rate->min_weight_lbs : null,
            'max_weight_lbs' => $rate->max_weight_lbs !== null ? (float) $rate->max_weight_lbs : null,
            'tier_label' => $this->tierLabel($rate),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fallbackRate(): array
    {
        $currency = app(TenantConfig::class)->currency();
        $perLb = (float) config('shipdjm.default_rate_per_lb', 500);

        return [
            'id' => null,
            'name' => 'Standard Air Shipping (placeholder)',
            'method' => 'standard',
            'currency' => $currency,
            'rate_per_lb' => $perLb,
            'minimum_charge' => $perLb,
            'handling_fee' => null,
            'min_weight_lbs' => null,
            'max_weight_lbs' => null,
            'tier_label' => 'All weights',
        ];
    }

    private function tierLabel(ShippingRate $rate): string
    {
        if ($rate->min_weight_lbs === null && $rate->max_weight_lbs === null) {
            return 'All weights';
        }

        if ($rate->min_weight_lbs !== null && $rate->max_weight_lbs !== null) {
            return "{$rate->min_weight_lbs}–{$rate->max_weight_lbs} lb";
        }

        if ($rate->min_weight_lbs !== null) {
            return "{$rate->min_weight_lbs}+ lb";
        }

        return "Up to {$rate->max_weight_lbs} lb";
    }
}
