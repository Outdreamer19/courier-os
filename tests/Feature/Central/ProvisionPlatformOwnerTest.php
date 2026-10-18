<?php

namespace Tests\Feature\Central;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProvisionPlatformOwnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_platform_owner(): void
    {
        $this->artisan('platform:owner', ['email' => 'Shane@Example.com', '--name' => 'Shane', '--password' => 'Secret-pass-123'])
            ->assertSuccessful();

        $user = User::query()->where('email', 'shane@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_PLATFORM_OWNER, $user->role);
        $this->assertNull($user->tenant_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('Secret-pass-123', $user->password));
    }

    public function test_running_again_resets_the_password_without_duplicating(): void
    {
        $this->artisan('platform:owner', ['email' => 'shane@example.com', '--password' => 'First-pass-123']);
        $this->artisan('platform:owner', ['email' => 'shane@example.com', '--password' => 'Second-pass-123']);

        $this->assertSame(1, User::query()->where('email', 'shane@example.com')->count());
        $this->assertTrue(Hash::check('Second-pass-123', User::query()->where('email', 'shane@example.com')->first()->password));
    }

    public function test_the_owner_can_reach_the_platform_console(): void
    {
        $this->artisan('platform:owner', ['email' => 'shane@example.com', '--password' => 'Secret-pass-123']);

        $this->post('http://courieros.co/login', ['email' => 'shane@example.com', 'password' => 'Secret-pass-123'])
            ->assertRedirect();

        $this->assertAuthenticated();
    }
}
