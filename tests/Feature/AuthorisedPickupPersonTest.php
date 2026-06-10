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

    public function test_customer_can_add_authorised_pickup_person(): void
    {
        $user = $this->customerWithProfile();

        $this->actingAs($user)
            ->post(route('portal.authorised-pickup-people.store'), [
                'full_name' => 'Alex Morgan',
                'phone' => '+1 (876) 555-0200',
                'relationship_note' => 'Spouse',
                'id_number' => 'TRN-998877',
            ])
            ->assertRedirect();

        $pickupPeople = $user->fresh()->customerProfile?->authorisedPickupPeople;

        $this->assertCount(1, $pickupPeople);
        $this->assertSame('Alex Morgan', $pickupPeople->first()->full_name);
        $this->assertSame('Spouse', $pickupPeople->first()->relationship_note);
    }

    public function test_customer_can_remove_authorised_pickup_person(): void
    {
        $user = $this->customerWithProfile();
        $pickupPerson = AuthorisedPickupPerson::create([
            'customer_profile_id' => $user->customerProfile->id,
            'full_name' => 'Alex Morgan',
            'phone' => '+1 (876) 555-0200',
        ]);

        $this->actingAs($user)
            ->delete(route('portal.authorised-pickup-people.destroy', [
                'authorised_pickup_person' => $pickupPerson,
            ]))
            ->assertRedirect();

        $this->assertCount(0, $user->fresh()->customerProfile?->authorisedPickupPeople ?? collect());
    }

    public function test_customer_cannot_remove_another_customers_pickup_person(): void
    {
        $user = $this->customerWithProfile();
        $otherUser = $this->customerWithProfile();
        $otherPickupPerson = AuthorisedPickupPerson::create([
            'customer_profile_id' => $otherUser->customerProfile->id,
            'full_name' => 'Someone Else',
            'phone' => '+1 (876) 555-0300',
        ]);

        $this->actingAs($user)
            ->delete(route('portal.authorised-pickup-people.destroy', [
                'authorised_pickup_person' => $otherPickupPerson,
            ]))
            ->assertNotFound();
    }

    public function test_customer_cannot_add_more_than_five_pickup_people(): void
    {
        $user = $this->customerWithProfile();

        foreach (range(1, AuthorisedPickupPerson::MAX_PER_CUSTOMER) as $index) {
            AuthorisedPickupPerson::create([
                'customer_profile_id' => $user->customerProfile->id,
                'full_name' => "Person {$index}",
                'phone' => "+1 (876) 555-010{$index}",
            ]);
        }

        $this->actingAs($user)
            ->post(route('portal.authorised-pickup-people.store'), [
                'full_name' => 'One Too Many',
                'phone' => '+1 (876) 555-0999',
            ])
            ->assertSessionHasErrors('full_name');

        $this->assertCount(
            AuthorisedPickupPerson::MAX_PER_CUSTOMER,
            $user->fresh()->customerProfile?->authorisedPickupPeople ?? collect(),
        );
    }

    public function test_profile_page_lists_authorised_pickup_people(): void
    {
        $user = $this->customerWithProfile();
        AuthorisedPickupPerson::create([
            'customer_profile_id' => $user->customerProfile->id,
            'full_name' => 'Alex Morgan',
            'phone' => '+1 (876) 555-0200',
            'relationship_note' => 'Sibling',
        ]);

        $this->actingAs($user)
            ->get(route('portal.profile.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('customer/Profile')
                ->has('authorisedPickupPeople', 1)
                ->where('authorisedPickupPeople.0.full_name', 'Alex Morgan')
                ->where('maxAuthorisedPickupPeople', AuthorisedPickupPerson::MAX_PER_CUSTOMER)
            );
    }

    public function test_admin_customer_show_includes_authorised_pickup_people(): void
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
                ->has('customer.authorised_pickup_people', 1)
                ->where('customer.authorised_pickup_people.0.full_name', 'Alex Morgan')
            );
    }
}
