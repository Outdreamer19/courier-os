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

    public function test_new_reference_uses_random_numeric_suffix(): void
    {
        /** @var CustomerReferenceGenerator $generator */
        $generator = $this->app->make(CustomerReferenceGenerator::class);

        $reference = $generator->next();

        $this->assertMatchesRegularExpression('/^SJM-\d{6}$/', $reference);
    }

    public function test_generated_references_are_unique(): void
    {
        /** @var CustomerReferenceGenerator $generator */
        $generator = $this->app->make(CustomerReferenceGenerator::class);

        $references = collect(range(1, 20))
            ->map(fn () => $generator->next())
            ->all();

        $this->assertCount(20, array_unique($references));
    }

    public function test_new_references_are_not_simple_sequential_increments(): void
    {
        /** @var CustomerReferenceGenerator $generator */
        $generator = $this->app->make(CustomerReferenceGenerator::class);

        $user = User::factory()->create();
        CustomerProfile::create([
            'user_id' => $user->id,
            'customer_reference' => 'SJM-000001',
            'trn' => '111111111',
            'phone' => '+1 (876) 555-0001',
            'jamaica_address' => 'Existing address',
            'date_of_birth' => '1990-01-01',
        ]);

        $next = $generator->next();

        $this->assertNotSame('SJM-000002', $next);
    }

    public function test_existing_customer_reference_records_continue_to_work(): void
    {
        $user = User::factory()->create();
        $profile = CustomerProfile::create([
            'user_id' => $user->id,
            'customer_reference' => 'SJM-000001',
            'trn' => '111111111',
            'phone' => '+1 (876) 555-0001',
            'jamaica_address' => 'Existing address',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->assertSame('SJM-000001', $user->fresh()->customerReference());
        $this->assertSame('SJM-000001', $profile->fresh()->customer_reference);
    }

    public function test_registration_creates_customer_profile_with_reference(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'trn' => '123456789',
            'phone' => '+1 (876) 555-0100',
            'jamaica_address' => '12 Hope Road',
            'date_of_birth' => '1995-06-15',
        ])->assertRedirect();

        $user = User::query()->where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
        $this->assertNotNull($user->customerProfile);
        $this->assertMatchesRegularExpression('/^SJM-\d{6}$/', $user->customerProfile->customer_reference);
    }
}
