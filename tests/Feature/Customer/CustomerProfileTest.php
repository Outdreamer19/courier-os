<?php

namespace Tests\Feature\Customer;

use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_profile_and_address_fields(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);

        CustomerProfile::factory()->create([
            'user_id' => $user->id,
            'phone' => null,
        ]);

        $this->actingAs($user)
            ->patch(route('portal.profile.update'), [
                'name' => 'Updated Name',
                'email' => $user->email,
                'trn' => '123456789',
                'phone' => '+1 (876) 555-9999',
                'whatsapp_number' => '+1 (876) 555-9998',
                'jamaica_address' => '1 Main Street',
                'parish' => 'St. Andrew',
                'date_of_birth' => '1990-01-15',
            ])
            ->assertRedirect(route('portal.profile.edit'));

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('+1 (876) 555-9999', $user->customerProfile?->phone);
        $this->assertSame('St. Andrew', $user->customerProfile?->parish);
    }
}
