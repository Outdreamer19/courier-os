<?php

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\CustomerWelcomeNotification;
use App\Notifications\PackageReadyForPickupNotification;
use App\Notifications\PackageStatusChangedNotification;
use App\Notifications\PreAlertStatusChangedNotification;
use App\Support\Tenancy\TenantAwareQueue;
use App\Support\Tenancy\TenantManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class QueuedNotificationTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_notifications_are_queued(): void
    {
        foreach ([
            CustomerWelcomeNotification::class,
            PackageStatusChangedNotification::class,
            PackageReadyForPickupNotification::class,
            PreAlertStatusChangedNotification::class,
        ] as $notification) {
            $this->assertContains(ShouldQueue::class, class_implements($notification), $notification);
        }
    }

    public function test_queued_notification_runs_inside_the_tenant_that_sent_it(): void
    {
        config(['queue.default' => 'database']);

        $tenant = Tenant::factory()->create(['subdomain' => 'acme']);
        $manager = app(TenantManager::class);
        $manager->set($tenant);

        $customer = User::factory()->create(['tenant_id' => $tenant->id]);
        $customer->notify(new CustomerWelcomeNotification($customer));

        $job = DB::table('jobs')->first();
        $this->assertNotNull($job, 'The welcome notification should be on the queue.');
        $this->assertSame($tenant->id, json_decode($job->payload, true)[TenantAwareQueue::PAYLOAD_KEY]);

        // A worker process starts with no tenant bound.
        $manager->forget();

        $tenantWhileSending = null;
        Event::listen(NotificationSent::class, function () use (&$tenantWhileSending, $manager): void {
            $tenantWhileSending = $manager->id();
        });

        Artisan::call('queue:work', ['connection' => 'database', '--once' => true, '--stop-when-empty' => true]);

        $this->assertSame($tenant->id, $tenantWhileSending);
        $this->assertFalse($manager->hasTenant(), 'The worker should clear the tenant after the job.');
        $this->assertSame(0, DB::table('jobs')->count());
        $this->assertSame(0, DB::table('failed_jobs')->count());
    }

    public function test_jobs_pushed_without_a_tenant_carry_no_tenant(): void
    {
        config(['queue.default' => 'database']);
        app(TenantManager::class)->forget();

        $customer = User::factory()->create();
        $customer->notify(new CustomerWelcomeNotification($customer));

        $payload = json_decode(DB::table('jobs')->value('payload'), true);
        $this->assertArrayNotHasKey(TenantAwareQueue::PAYLOAD_KEY, $payload);
    }
}
