<?php

namespace Database\Factories;

use App\Models\WarehouseAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseAddress>
 */
class WarehouseAddressFactory extends Factory
{
    protected $model = WarehouseAddress::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Test Warehouse',
            'address_line_1' => fake()->streetAddress(),
            'city' => 'Miami',
            'state' => 'FL',
            'zip' => '33101',
            'phone' => '+1 (555) 000-0000',
            'instructions' => 'Use your customer reference as the suite number.',
            'is_active' => false,
        ];
    }
}
