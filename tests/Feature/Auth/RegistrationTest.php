<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\CustomerWelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'trn' => '123456789',
            'phone' => '+1 (876) 555-0100',
            'jamaica_address' => '12 Hope Road',
            'date_of_birth' => '1990-01-15',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_users_receive_a_welcome_email()
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'welcome@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'trn' => '123456789',
            'phone' => '+1 (876) 555-0100',
            'jamaica_address' => '12 Hope Road',
            'date_of_birth' => '1990-01-15',
        ]);

        $user = User::where('email', 'welcome@example.com')->firstOrFail();

        Notification::assertSentTo($user, CustomerWelcomeNotification::class);
    }
}
