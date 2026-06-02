<?php

namespace Database\Factories;

use App\Enums\Carrier;
use App\Enums\PackageStatus;
use App\Enums\PaymentStatus;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'package_reference' => 'PKG-'.fake()->unique()->numerify('######'),
            'tracking_number' => strtoupper(fake()->bothify('PKG##########')),
            'merchant_name' => fake()->randomElement(['Amazon', 'Walmart', 'SHEIN']),
            'carrier' => fake()->randomElement(Carrier::cases()),
            'weight_lbs' => fake()->randomFloat(2, 1, 25),
            'declared_value' => fake()->randomFloat(2, 20, 400),
            'amount_due' => fake()->randomFloat(2, 500, 15000),
            'payment_status' => PaymentStatus::Unpaid,
            'status' => PackageStatus::AwaitingArrival,
        ];
    }

    public function readyForPickup(): static
    {
        return $this->state(fn () => [
            'status' => PackageStatus::ReadyForPickup,
            'ready_for_pickup_at' => now(),
            'received_at_warehouse_at' => now()->subWeeks(2),
            'shipped_to_jamaica_at' => now()->subWeek(),
            'arrived_in_jamaica_at' => now()->subDays(3),
        ]);
    }
}
