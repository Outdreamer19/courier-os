<?php

namespace Tests\Feature\Customer;

use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);

        CustomerProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        return $user;
    }

    public function test_customer_can_view_their_packages(): void
    {
        $user = $this->customer();

        Package::factory()->create([
            'user_id' => $user->id,
            'package_reference' => 'PKG-TEST01',
        ]);

        $this->actingAs($user)
            ->get(route('portal.packages.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('portal.packages.show', Package::query()->first()))
            ->assertOk();
    }

    public function test_customer_cannot_view_another_customers_package(): void
    {
        $owner = $this->customer();
        $other = $this->customer();

        $package = Package::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($other)
            ->get(route('portal.packages.show', $package))
            ->assertForbidden();
    }
}
