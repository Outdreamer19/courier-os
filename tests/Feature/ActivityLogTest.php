<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\CustomerProfile;
use App\Models\PreAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_profile_update_creates_activity_log(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        CustomerProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch(route('portal.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'trn' => '123456789',
                'phone' => '+1 (876) 555-0100',
                'jamaica_address' => '1 Main Street',
                'date_of_birth' => '1990-01-15',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => ActivityLog::ACTION_CUSTOMER_PROFILE_UPDATED,
        ]);
    }

    public function test_admin_pre_alert_status_change_creates_activity_log(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        CustomerProfile::factory()->create(['user_id' => $customer->id]);
        $preAlert = PreAlert::factory()->create(['user_id' => $customer->id]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.pre-alerts.update', ['pre_alert' => $preAlert]), [
                'status' => \App\Enums\PreAlertStatus::UnderReview->value,
                'admin_notes' => 'Reviewing invoice',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => ActivityLog::ACTION_PRE_ALERT_STATUS_CHANGED,
            'subject_type' => 'PreAlert',
            'subject_id' => $preAlert->id,
        ]);
    }
}
