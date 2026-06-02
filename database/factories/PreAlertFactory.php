<?php

namespace Database\Factories;

use App\Enums\Carrier;
use App\Enums\PreAlertStatus;
use App\Models\PreAlert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreAlert>
 */
class PreAlertFactory extends Factory
{
    protected $model = PreAlert::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'merchant_name' => fake()->randomElement(['Amazon', 'Walmart', 'SHEIN', 'eBay']),
            'order_number' => strtoupper(fake()->bothify('ORD-########')),
            'tracking_number' => strtoupper(fake()->bothify('TRK##########')),
            'carrier' => fake()->randomElement(Carrier::cases()),
            'expected_delivery_date' => fake()->optional()->dateTimeBetween('now', '+2 weeks'),
            'item_description' => fake()->sentence(8),
            'declared_value' => fake()->optional()->randomFloat(2, 10, 500),
            'status' => PreAlertStatus::Submitted,
            'customer_notes' => fake()->optional()->sentence(),
        ];
    }
}
