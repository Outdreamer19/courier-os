<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_customers_but_cannot_create_them(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('admin.customers.index'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('admin.customers.create'))
            ->assertForbidden();
    }

    public function test_staff_cannot_access_contact_messages_or_system_settings(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('admin.contact-messages.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.shipping-rates.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.warehouse.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_manage_admin_users_or_activity_logs(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.admin-users.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertForbidden();
    }

    public function test_owner_can_manage_admin_users(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.admin-users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/admin-users/Index'));

        $this->actingAs($owner)
            ->post(route('admin.admin-users.store'), [
                'name' => 'Warehouse Staff',
                'email' => 'staff@shipdjm.test',
                'password' => 'password',
                'role' => User::ROLE_STAFF,
                'status' => User::STATUS_ACTIVE,
            ])
            ->assertRedirect(route('admin.admin-users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'staff@shipdjm.test',
            'role' => User::ROLE_STAFF,
        ]);
    }

    public function test_owner_can_view_activity_logs(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/activity-logs/Index'));
    }

    public function test_staff_cannot_delete_contact_messages(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->delete(route('admin.contact-messages.destroy', [
                'contact_message' => ContactMessage::create([
                    'name' => 'Guest',
                    'email' => 'guest@test.com',
                    'subject' => 'Hello',
                    'message' => 'Need help',
                    'status' => ContactMessage::STATUS_NEW,
                ]),
            ]))
            ->assertForbidden();
    }
}
