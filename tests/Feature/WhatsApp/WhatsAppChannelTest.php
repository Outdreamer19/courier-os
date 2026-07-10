<?php

namespace Tests\Feature\WhatsApp;

use App\Channels\WhatsApp\WhatsAppMessage;
use App\Enums\PackageStatus;
use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PackageReadyForPickupNotification;
use App\Notifications\PackageStatusChangedNotification;
use App\Support\Tenancy\TenantManager;
use Database\Seeders\CourierOsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppChannelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'localhost']);
        $this->seed(CourierOsSeeder::class);
    }

    // -------------------------------------------------------------------------
    // via() behaviour
    // -------------------------------------------------------------------------

    public function test_via_includes_whatsapp_when_tenant_has_api_credentials(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'island')->first();
        $tenant->update([
            'whatsapp_api_token' => 'test-token',
            'whatsapp_phone_number_id' => '123456789',
        ]);
        app(TenantManager::class)->set($tenant);

        $package = Package::factory()->make();
        $notification = new PackageStatusChangedNotification(
            $package,
            PackageStatus::Pending,
            PackageStatus::Received,
        );

        $this->assertContains('whatsapp', $notification->via(new \stdClass));
    }

    public function test_via_excludes_whatsapp_when_tenant_has_no_api_credentials(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'island')->first();
        // Ensure no credentials
        $tenant->update(['whatsapp_api_token' => null, 'whatsapp_phone_number_id' => null]);
        app(TenantManager::class)->set($tenant);

        $package = Package::factory()->make();
        $notification = new PackageStatusChangedNotification(
            $package,
            PackageStatus::Pending,
            PackageStatus::Received,
        );

        $this->assertNotContains('whatsapp', $notification->via(new \stdClass));
        $this->assertContains('mail', $notification->via(new \stdClass));
    }

    public function test_via_excludes_whatsapp_when_no_tenant_bound(): void
    {
        // Central / no tenant context
        app(TenantManager::class)->forget();
        config(['services.whatsapp.api_token' => null, 'services.whatsapp.phone_number_id' => null]);

        $package = Package::factory()->make();
        $notification = new PackageStatusChangedNotification(
            $package,
            null,
            PackageStatus::Received,
        );

        $this->assertNotContains('whatsapp', $notification->via(new \stdClass));
    }

    // -------------------------------------------------------------------------
    // WhatsApp channel actually sends
    // -------------------------------------------------------------------------

    public function test_channel_sends_whatsapp_message_when_customer_has_number(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'island')->first();
        $tenant->update([
            'whatsapp_api_token' => 'test-token',
            'whatsapp_phone_number_id' => '123456',
        ]);
        app(TenantManager::class)->set($tenant);

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        CustomerProfile::factory()->create([
            'user_id' => $customer->id,
            'whatsapp_number' => '+18765550001',
        ]);

        $package = Package::factory()->create(['user_id' => $customer->id]);

        // Use Http::fake() so the real WhatsAppClient's HTTP call is intercepted
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200)]);

        $customer->notify(new PackageStatusChangedNotification($package, null, PackageStatus::Received));

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'graph.facebook.com')
                && $request['type'] === 'template';
        });
    }

    public function test_channel_skips_send_when_customer_has_no_whatsapp_number(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'island')->first();
        $tenant->update([
            'whatsapp_api_token' => 'test-token',
            'whatsapp_phone_number_id' => '123456',
        ]);
        app(TenantManager::class)->set($tenant);

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        CustomerProfile::factory()->create([
            'user_id' => $customer->id,
            'whatsapp_number' => null,
        ]);

        $package = Package::factory()->create(['user_id' => $customer->id]);

        // routeNotificationForWhatsapp() returns null → channel should no-op
        $this->assertNull($customer->routeNotificationForWhatsapp());

        Http::fake(['graph.facebook.com/*' => Http::response([], 200)]);

        $customer->notify(new PackageStatusChangedNotification($package, null, PackageStatus::Received));

        Http::assertNothingSent();
    }

    // -------------------------------------------------------------------------
    // toWhatsApp() payload shape
    // -------------------------------------------------------------------------

    public function test_package_status_notification_builds_correct_whatsapp_payload(): void
    {
        $customer = User::factory()->make(['name' => 'Bob Smith']);
        $package = Package::factory()->make(['package_reference' => 'PKG-999']);

        $notification = new PackageStatusChangedNotification($package, null, PackageStatus::ReadyForPickup);
        $message = $notification->toWhatsApp($customer);

        $this->assertInstanceOf(WhatsAppMessage::class, $message);
        $this->assertContains('Bob Smith', $message->bodyParams);
        $this->assertContains('PKG-999', $message->bodyParams);
        $this->assertContains(PackageStatus::ReadyForPickup->label(), $message->bodyParams);
    }

    public function test_package_ready_for_pickup_notification_includes_amount_in_whatsapp_payload(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'island')->first();
        app(TenantManager::class)->set($tenant);

        $customer = User::factory()->make(['name' => 'Alice']);
        $package = Package::factory()->make([
            'package_reference' => 'PKG-42',
            'amount_due' => 1500.00,
        ]);

        $notification = new PackageReadyForPickupNotification($package);
        $message = $notification->toWhatsApp($customer);

        $this->assertContains('USD 1,500.00', $message->bodyParams);
    }
}
