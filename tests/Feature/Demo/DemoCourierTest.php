<?php

namespace Tests\Feature\Demo;

use App\Http\Middleware\GuardDemoTenant;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\CustomerWelcomeNotification;
use App\Support\Demo\DemoMode;
use App\Support\Tenancy\TenantManager;
use Database\Seeders\CourierOsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoCourierTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'http://demo.courieros.co';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function resetDemo(): Tenant
    {
        $this->artisan('demo:reset')->assertSuccessful();

        return Tenant::query()->where('subdomain', 'demo')->firstOrFail();
    }

    private function demoUser(string $email): User
    {
        $tenant = Tenant::query()->where('subdomain', 'demo')->firstOrFail();

        return User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->firstOrFail();
    }

    public function test_reset_creates_the_fictional_courier_with_data(): void
    {
        $tenant = $this->resetDemo();

        $this->assertSame(DemoMode::COURIER_NAME, $tenant->name);
        $this->assertTrue($tenant->isDemo());
        $this->assertTrue($tenant->onGenericTrial());
        Storage::disk('public')->assertExists($tenant->logo_path);

        app(TenantManager::class)->set($tenant);
        $this->assertGreaterThan(40, Package::query()->count());
        $this->assertSame(5, Package::query()
            ->whereHas('user', fn ($q) => $q->where('email', config('courieros.demo.customer_email')))
            ->count());
        app(TenantManager::class)->forget();
    }

    public function test_reset_is_repeatable_and_never_touches_other_couriers(): void
    {
        $this->seed(CourierOsSeeder::class);
        $others = Package::withoutGlobalScope('tenant')->count();

        $first = $this->resetDemo();
        $demoPackages = Package::withoutGlobalScope('tenant')->where('tenant_id', $first->id)->count();

        $second = $this->resetDemo();

        $this->assertSame(1, Tenant::query()->where('subdomain', 'demo')->count());
        $this->assertSame($demoPackages, Package::withoutGlobalScope('tenant')->where('tenant_id', $second->id)->count());
        $this->assertSame($others, Package::withoutGlobalScope('tenant')->where('tenant_id', '!=', $second->id)->count());
        $this->assertSame(0, Package::withoutGlobalScope('tenant')->where('tenant_id', $first->id)->count());
    }

    public function test_reset_refuses_a_demo_subdomain_tenant_with_billing(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Real Co', 'subdomain' => 'demo', 'status' => Tenant::STATUS_ACTIVE, 'customer_reference_prefix' => 'RC']);
        $tenant->forceFill(['stripe_id' => 'cus_real'])->save();

        $this->artisan('demo:reset')->assertFailed();

        $this->assertSame('Real Co', $tenant->fresh()->name);
    }

    public function test_demo_subdomain_resolves_and_shares_demo_logins(): void
    {
        $this->resetDemo();

        $this->get(self::HOST.'/login')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('auth/Login')
                ->where('brand.name', DemoMode::COURIER_NAME)
                ->where('demo.customer_email', config('courieros.demo.customer_email'))
                ->where('demo.admin_email', config('courieros.demo.admin_email'))
                ->where('demo.password', config('courieros.demo.password')));
    }

    public function test_other_couriers_do_not_get_demo_props(): void
    {
        $this->seed(CourierOsSeeder::class);

        $this->get('http://shipd.courieros.co/login')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('demo', null));
    }

    public function test_public_demo_logins_sign_in(): void
    {
        $this->resetDemo();

        foreach (['customer_email', 'admin_email'] as $key) {
            $this->post(self::HOST.'/login', [
                'email' => config("courieros.demo.{$key}"),
                'password' => config('courieros.demo.password'),
            ])->assertRedirect();

            $this->assertAuthenticated();
            auth()->logout();
        }
    }

    public function test_signup_cannot_claim_the_demo_subdomain(): void
    {
        $this->post('http://courieros.co/signup', [
            'business_name' => 'Sneaky Courier',
            'subdomain' => 'demo',
            'currency' => 'USD',
            'customer_reference_prefix' => 'SNK',
            'owner_name' => 'Sam',
            'owner_email' => 'sam@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('subdomain');
    }

    public function test_demo_blocks_changing_a_login(): void
    {
        $this->resetDemo();
        $admin = $this->demoUser(config('courieros.demo.admin_email'));

        $this->actingAs($admin)
            ->patch(self::HOST.'/settings/profile', ['name' => 'Hacker', 'email' => 'hacker@example.com'])
            ->assertRedirect()
            ->assertSessionHas('error', GuardDemoTenant::MESSAGE);

        $this->assertSame(config('courieros.demo.admin_email'), $admin->fresh()->email);

        $this->actingAs($admin)
            ->put(self::HOST.'/settings/password', [
                'current_password' => config('courieros.demo.password'),
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertSessionHas('error', GuardDemoTenant::MESSAGE);
    }

    public function test_demo_admin_cannot_edit_the_public_customer_login(): void
    {
        $this->resetDemo();
        $admin = $this->demoUser(config('courieros.demo.admin_email'));
        $customer = $this->demoUser(config('courieros.demo.customer_email'));

        $this->actingAs($admin)
            ->put(self::HOST."/admin/customers/{$customer->id}", ['name' => 'Changed', 'email' => 'changed@example.com'])
            ->assertSessionHas('error', GuardDemoTenant::MESSAGE);

        $this->assertSame(config('courieros.demo.customer_email'), $customer->fresh()->email);
    }

    public function test_demo_blocks_registration(): void
    {
        $this->resetDemo();

        $this->post(self::HOST.'/register', [
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHas('error', GuardDemoTenant::MESSAGE);

        $this->assertNull(User::withoutGlobalScope('tenant')->where('email', 'visitor@example.com')->first());
    }

    public function test_guard_does_nothing_on_real_couriers(): void
    {
        $this->seed(CourierOsSeeder::class);
        $tenant = Tenant::query()->where('subdomain', 'shipd')->firstOrFail();
        $owner = User::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->where('role', User::ROLE_OWNER)->firstOrFail();

        $this->actingAs($owner)
            ->patch('http://shipd.courieros.co/settings/profile', ['name' => 'New Name', 'email' => $owner->email])
            ->assertSessionMissing('error');

        $this->assertSame('New Name', $owner->fresh()->name);
    }

    public function test_notifications_to_demo_accounts_are_cancelled(): void
    {
        $this->resetDemo();
        $customer = $this->demoUser(config('courieros.demo.customer_email'));

        $result = Event::until(new NotificationSending($customer, new CustomerWelcomeNotification($customer), 'mail'));

        $this->assertFalse($result);
    }

    public function test_notifications_to_real_couriers_still_send(): void
    {
        $this->seed(CourierOsSeeder::class);
        $customer = User::withoutGlobalScope('tenant')->where('email', 'customer@shipd.test')->firstOrFail();

        $this->assertNull(Event::until(new NotificationSending($customer, new CustomerWelcomeNotification($customer), 'mail')));
    }

    public function test_jamaica_page_links_the_live_demo(): void
    {
        $this->get('http://courieros.co/jamaica')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('liveDemo.url', 'https://demo.courieros.co/login')
                ->where('liveDemo.name', DemoMode::COURIER_NAME));
    }

    public function test_switching_the_demo_off_makes_the_subdomain_central_again(): void
    {
        config(['courieros.demo.enabled' => false]);

        $this->get('http://courieros.co/jamaica')
            ->assertInertia(fn ($page) => $page->where('liveDemo', null));

        $this->artisan('demo:reset')->assertSuccessful();
        $this->assertNull(Tenant::query()->where('subdomain', 'demo')->first());
    }
}
