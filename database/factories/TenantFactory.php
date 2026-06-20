<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();
        $slug = Str::slug($name);

        return [
            'name' => $name,
            'subdomain' => $slug,
            'custom_domain' => null,
            'status' => Tenant::STATUS_ACTIVE,
            'currency' => fake()->randomElement(['USD', 'JMD']),
            'customer_reference_prefix' => strtoupper(Str::substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3) ?: 'CUS'),
            'package_reference_prefix' => 'PKG',
            'whatsapp_number' => null,
            'logo_path' => null,
            'brand_primary_color' => null,
            'trial_ends_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => Tenant::STATUS_PENDING]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => Tenant::STATUS_SUSPENDED]);
    }

    public function currency(string $currency): static
    {
        return $this->state(fn () => ['currency' => $currency]);
    }
}
