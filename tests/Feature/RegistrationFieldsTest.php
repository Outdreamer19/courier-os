<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validRegistrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'trn' => '123456789',
            'phone' => '+1 (876) 555-0100',
            'jamaica_address' => '12 Hope Road, Kingston',
            'date_of_birth' => '1995-06-15',
        ], $overrides);
    }

    public function test_registration_requires_trn_phone_address_and_date_of_birth(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(['trn', 'phone', 'jamaica_address', 'date_of_birth']);
    }

    public function test_under_18_customers_cannot_register(): void
    {
        $this->post(route('register.store'), $this->validRegistrationPayload([
            'date_of_birth' => now()->subYears(17)->toDateString(),
        ]))->assertSessionHasErrors('date_of_birth');

        $this->assertDatabaseMissing('users', [
            'email' => 'jane@example.com',
        ]);
    }

    public function test_registration_persists_customer_profile_fields(): void
    {
        $this->post(route('register.store'), $this->validRegistrationPayload())
            ->assertRedirect();

        $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

        $this->assertSame('123456789', $user->customerProfile?->trn);
        $this->assertSame('+1 (876) 555-0100', $user->customerProfile?->phone);
        $this->assertSame('12 Hope Road, Kingston', $user->customerProfile?->jamaica_address);
        $this->assertSame('1995-06-15', $user->customerProfile?->date_of_birth?->toDateString());
    }

    public function test_registration_creates_activity_log_entry(): void
    {
        $this->post(route('register.store'), $this->validRegistrationPayload([
            'email' => 'logged@example.com',
        ]))->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityLog::ACTION_CUSTOMER_REGISTERED,
        ]);
    }
}
