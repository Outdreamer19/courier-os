<?php

namespace Tests\Feature;

use App\Models\AuthorisedPickupPerson;
use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorisedPickupPersonTest extends TestCase
{
    use RefreshDatabase;

    private function customerWithProfile(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        CustomerProfile::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_customer_can_save_authorised_pickup_person(): void
    {
        $user = $this->customerWithProfile();

        $this->actingAs($user)
            ->patch(route('portal.authorised-pickup-person.update'), [
                'full_name' => 'Alex Morgan',
                'phone' => '+1 (876) 555-0200',
                'relationship_note' => 'Spouse',
                'id_number' => 'TRN-998877',
            ])
            ->assertRedirect();

        $pickupPerson = $user->fresh()->customerProfile?->authorisedPickupPerson;

        $this->assertInstanceOf(AuthorisedPickupPerson::class, $pickupPerson);
        $this->assertSame('Alex Morgan', $pickupPerson->full_name);
        $this->assertSame('Spouse', $pickupPerson->relationship_note);
    }

    public function test_customer_can_remove_authorised_pickup_person(): void
    {
        $user = $this->customerWithProfile();
        AuthorisedPickupPerson::create([
            'customer_profile_id' => $user->customerProfile->id,
            'full_name' => 'Alex Morgan',
            'phone' => '+1 (876) 555-0200',
        ]);

        $this->actingAs($user)
            ->patch(route('portal.authorised-pickup-person.update'), [
                'remove' => true,
            ])
            ->assertRedirect();

        $this->assertNull($user->fresh()->customerProfile?->authorisedPickupPerson);
    }

    public function test_admin_customer_show_includes_authorised_pickup_person(): void
    {
        $user = $this->customerWithProfile();
        AuthorisedPickupPerson::create([
            'customer_profile_id' => $user->customerProfile->id,
            'full_name' => 'Alex Morgan',
            'phone' => '+1 (876) 555-0200',
            'relationship_note' => 'Sibling',
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.customers.show', ['customer' => $user]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/customers/Show')
                ->where('customer.authorised_pickup_person.full_name', 'Alex Morgan')
            );
    }
}
