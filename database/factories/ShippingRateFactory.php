<?php

namespace Database\Factories;

use App\Models\ShippingRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingRate>
 */
class ShippingRateFactory extends Factory
{
    protected $model = ShippingRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Standard Air Shipping',
            'method' => 'standard',
            'currency' => 'JMD',
            'rate_per_lb' => 500,
            'minimum_charge' => 500,
            'handling_fee' => null,
            'is_active' => true,
        ];
    }
}
