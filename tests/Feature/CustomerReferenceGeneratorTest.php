<?php

namespace Tests\Feature;

use App\Models\CustomerProfile;
use App\Models\User;
use App\Services\CustomerReferenceGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerReferenceGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_reference_starts_at_one(): void
    {
        /** @var CustomerReferenceGenerator $generator */
        $generator = $this->app->make(CustomerReferenceGenerator::class);

        $this->assertSame('SJM-000001', $generator->next());
    }

    public function test_subsequent_references_increment(): void
    {
        /** @var CustomerReferenceGenerator $generator */
        $generator = $this->app->make(CustomerReferenceGenerator::class);

        $user = User::factory()->create();
        CustomerProfile::create([
            'user_id' => $user->id,
            'customer_reference' => $generator->next(),
        ]);

        $this->assertSame('SJM-000002', $generator->next());
    }

    public function test_registration_creates_customer_profile_with_reference(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect();

        $user = User::query()->where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
        $this->assertNotNull($user->customerProfile);
        $this->assertMatchesRegularExpression('/^SJM-\d{6}$/', $user->customerProfile->customer_reference);
    }
}
