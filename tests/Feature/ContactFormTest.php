<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_submit_a_contact_message(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Marsha Brown',
            'email' => 'marsha@example.com',
            'phone' => '+18761234567',
            'subject' => 'Quick question',
            'message' => 'How long do packages usually take?',
        ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'marsha@example.com',
            'subject' => 'Quick question',
            'status' => ContactMessage::STATUS_NEW,
        ]);
    }

    public function test_contact_form_requires_valid_input(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }
}
