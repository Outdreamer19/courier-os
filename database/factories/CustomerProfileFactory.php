<?php

namespace Database\Factories;

use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerProfile>
 */
class CustomerProfileFactory extends Factory
{
    protected $model = CustomerProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_reference' => 'DJM-'.fake()->unique()->numerify('######'),
            'phone' => fake()->optional()->phoneNumber(),
            'whatsapp_number' => fake()->optional()->phoneNumber(),
            'jamaica_address' => fake()->optional()->streetAddress(),
            'parish' => fake()->optional()->randomElement([
                'Kingston',
                'St. Andrew',
                'St. Catherine',
                'Clarendon',
            ]),
        ];
    }
}
