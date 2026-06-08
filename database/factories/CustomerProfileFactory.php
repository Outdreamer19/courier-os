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
            'customer_reference' => config('shipdjm.customer_reference.prefix', 'SJM').'-'.fake()->unique()->numerify('######'),
            'trn' => fake()->numerify('#########'),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-19 years')->format('Y-m-d'),
            'phone' => fake()->phoneNumber(),
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
