<?php

namespace Tests\Feature\PlatformSupport;

use App\Models\PlatformSupportMessage;
use App\Models\PlatformSupportThread;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PlatformSupportMessageFromPlatform;
use App\Notifications\PlatformSupportMessageFromTenant;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PlatformSupportMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'courieros.co']);
    }

    private function tenantUser(Tenant $tenant, string $role): User
    {
        app(TenantManager::class)->set($tenant);

        $user = User::factory()->create([
            'role' => $role,
            'tenant_id' => $tenant->id,
        ]);

        app(TenantManager::class)->forget();

        return $user;
    }

    private function platformOwner(): User
    {
        app(TenantManager::class)->forget();

        return User::factory()->create([
            'role' => User::ROLE_PLATFORM_OWNER,
            'tenant_id' => null,
        ]);
    }

    public function test_owner_can_create_and_list_support_threads(): void
    {
        Notification::fake();

        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $owner = $this->tenantUser($tenant, User::ROLE_OWNER);
        $this->platformOwner();

        $this->actingAs($owner)
            ->post('http://acme.courieros.co/admin/platform-support', [
                'subject' => 'Billing question',
                'body' => 'Can we change our plan?',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('platform_support_threads', [
            'tenant_id' => $tenant->id,
            'subject' => 'Billing question',
            'status' => PlatformSupportThread::STATUS_OPEN,
        ]);

        $this->actingAs($owner)
            ->get('http://acme.courieros.co/admin/platform-support')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/platform-support/Index')
                ->has('threads.data', 1)
            );

        $platform = User::query()
            ->withoutGlobalScope('tenant')
            ->where('role', User::ROLE_PLATFORM_OWNER)
            ->firstOrFail();

        Notification::assertSentTo($platform, PlatformSupportMessageFromTenant::class);
    }

    public function test_admin_can_access_platform_support(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $admin = $this->tenantUser($tenant, User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get('http://acme.courieros.co/admin/platform-support')
            ->assertOk();
    }

    public function test_staff_cannot_access_platform_support(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $staff = $this->tenantUser($tenant, User::ROLE_STAFF);

        $this->actingAs($staff)
            ->get('http://acme.courieros.co/admin/platform-support')
            ->assertForbidden();
    }

    public function test_tenant_cannot_open_another_tenants_thread(): void
    {
        $acme = Tenant::factory()->create(['subdomain' => 'acme']);
        $rival = Tenant::factory()->create(['subdomain' => 'rival']);
        $acmeOwner = $this->tenantUser($acme, User::ROLE_OWNER);
        $rivalOwner = $this->tenantUser($rival, User::ROLE_OWNER);

        Notification::fake();

        $this->actingAs($rivalOwner)
            ->post('http://rival.courieros.co/admin/platform-support', [
                'subject' => 'Rival secret',
                'body' => 'Private to rival',
            ]);

        $thread = PlatformSupportThread::query()->firstOrFail();

        $this->actingAs($acmeOwner)
            ->get("http://acme.courieros.co/admin/platform-support/{$thread->id}")
            ->assertNotFound();
    }

    public function test_platform_owner_sees_all_threads_and_can_reply(): void
    {
        Notification::fake();

        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $owner = $this->tenantUser($tenant, User::ROLE_OWNER);
        $platform = $this->platformOwner();

        $this->actingAs($owner)
            ->post('http://acme.courieros.co/admin/platform-support', [
                'subject' => 'Need help',
                'body' => 'Something broke',
            ]);

        $thread = PlatformSupportThread::query()->firstOrFail();

        $this->actingAs($platform)
            ->get('http://courieros.co/platform/support')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('central/admin/support/Index')
                ->has('threads.data', 1)
            );

        $this->actingAs($platform)
            ->post("http://courieros.co/platform/support/{$thread->id}/messages", [
                'body' => 'Looking into it now.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('platform_support_messages', [
            'thread_id' => $thread->id,
            'author_side' => PlatformSupportMessage::SIDE_PLATFORM,
            'body' => 'Looking into it now.',
        ]);

        Notification::assertSentTo($owner, PlatformSupportMessageFromPlatform::class);
    }

    public function test_non_platform_owner_cannot_access_platform_support_inbox(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $owner = $this->tenantUser($tenant, User::ROLE_OWNER);

        $this->actingAs($owner)
            ->get('http://courieros.co/platform/support')
            ->assertForbidden();
    }

    public function test_marking_a_thread_read_clears_unread_for_that_user_only(): void
    {
        Notification::fake();

        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $owner = $this->tenantUser($tenant, User::ROLE_OWNER);
        $admin = $this->tenantUser($tenant, User::ROLE_ADMIN);
        $platform = $this->platformOwner();

        $this->actingAs($owner)
            ->post('http://acme.courieros.co/admin/platform-support', [
                'subject' => 'Shared thread',
                'body' => 'Initial',
            ]);

        $thread = PlatformSupportThread::query()->firstOrFail();

        $this->actingAs($platform)
            ->post("http://courieros.co/platform/support/{$thread->id}/messages", [
                'body' => 'Platform reply',
            ]);

        $service = app(\App\Services\Platform\PlatformSupportService::class);

        $this->assertSame(1, $service->unreadCountFor($owner));
        $this->assertSame(1, $service->unreadCountFor($admin));

        $this->actingAs($owner)
            ->get("http://acme.courieros.co/admin/platform-support/{$thread->id}")
            ->assertOk();

        $this->assertSame(0, $service->unreadCountFor($owner->fresh()));
        $this->assertSame(1, $service->unreadCountFor($admin->fresh()));
    }

    public function test_reply_auto_reopens_a_closed_thread(): void
    {
        Notification::fake();

        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $owner = $this->tenantUser($tenant, User::ROLE_OWNER);
        $platform = $this->platformOwner();

        $this->actingAs($owner)
            ->post('http://acme.courieros.co/admin/platform-support', [
                'subject' => 'Close me',
                'body' => 'Initial',
            ]);

        $thread = PlatformSupportThread::query()->firstOrFail();

        $this->actingAs($owner)
            ->patch("http://acme.courieros.co/admin/platform-support/{$thread->id}", [
                'status' => PlatformSupportThread::STATUS_CLOSED,
            ])
            ->assertRedirect();

        $this->assertSame(PlatformSupportThread::STATUS_CLOSED, $thread->fresh()->status);

        $this->actingAs($platform)
            ->post("http://courieros.co/platform/support/{$thread->id}/messages", [
                'body' => 'Reopening with a reply',
            ])
            ->assertRedirect();

        $this->assertSame(PlatformSupportThread::STATUS_OPEN, $thread->fresh()->status);
        $this->assertNull($thread->fresh()->closed_at);
    }

    public function test_platform_owner_can_start_a_thread_with_a_tenant(): void
    {
        Notification::fake();

        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $owner = $this->tenantUser($tenant, User::ROLE_OWNER);
        $platform = $this->platformOwner();

        $this->actingAs($platform)
            ->post('http://courieros.co/platform/support', [
                'tenant_id' => $tenant->id,
                'subject' => 'Quick check-in',
                'body' => 'How is onboarding going?',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('platform_support_threads', [
            'tenant_id' => $tenant->id,
            'subject' => 'Quick check-in',
        ]);

        Notification::assertSentTo($owner, PlatformSupportMessageFromPlatform::class);
    }
}
