<?php

namespace Tests\Feature\Settings;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Database\Seeders\CourierOsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'localhost']);
        $this->seed(CourierOsSeeder::class);
    }

    private function setTenant(string $subdomain): Tenant
    {
        $tenant = Tenant::query()->where('subdomain', $subdomain)->firstOrFail();
        app(TenantManager::class)->set($tenant);

        return $tenant;
    }

    private function ownerFor(string $subdomain): User
    {
        $this->setTenant($subdomain);

        return User::query()->where('email', "owner@{$subdomain}.test")->firstOrFail();
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->setTenant('island');

        $this->get('http://island.localhost/settings/branding')
            ->assertRedirect('/login');
    }

    public function test_customer_cannot_access_branding_settings(): void
    {
        $this->setTenant('island');
        $customer = User::query()->where('email', 'customer@island.test')->firstOrFail();

        $this->actingAs($customer)
            ->get('http://island.localhost/settings/branding')
            ->assertForbidden();
    }

    public function test_owner_can_view_branding_settings(): void
    {
        $owner = $this->ownerFor('island');

        $this->actingAs($owner)
            ->get('http://island.localhost/settings/branding')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('settings/Branding')
                ->has('tenant.name')
                ->has('tenant.currency')
                ->has('tenant.brand_primary_color')
                ->has('tenant.brand_accent_color')
                ->has('tenant.logo_path')
                ->has('currencies'),
            );
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_owner_can_update_business_name(): void
    {
        $owner = $this->ownerFor('island');
        $tenant = Tenant::query()->where('subdomain', 'island')->firstOrFail();

        $this->actingAs($owner)
            ->patch('http://island.localhost/settings/branding', [
                'name' => 'Island Express Courier',
                'currency' => 'USD',
                'brand_primary_color' => '#F5C01E',
            ])
            ->assertRedirect(route('branding.edit'));

        $this->assertSame('Island Express Courier', $tenant->fresh()->name);
    }

    public function test_owner_can_update_primary_and_accent_colors(): void
    {
        $owner = $this->ownerFor('island');
        $tenant = Tenant::query()->where('subdomain', 'island')->firstOrFail();

        $this->actingAs($owner)
            ->patch('http://island.localhost/settings/branding', [
                'name' => $tenant->name,
                'currency' => $tenant->currency,
                'brand_primary_color' => '#12275E',
                'brand_accent_color' => '#D0202E',
            ])
            ->assertSessionHasNoErrors();

        $tenant->refresh();
        $this->assertSame('#12275E', $tenant->brand_primary_color);
        $this->assertSame('#D0202E', $tenant->brand_accent_color);
    }

    public function test_invalid_hex_accent_color_fails_validation(): void
    {
        $owner = $this->ownerFor('island');
        $tenant = Tenant::query()->where('subdomain', 'island')->firstOrFail();

        $this->actingAs($owner)
            ->patch('http://island.localhost/settings/branding', [
                'name' => $tenant->name,
                'currency' => $tenant->currency,
                'brand_primary_color' => null,
                'brand_accent_color' => 'not-a-color',
            ])
            ->assertSessionHasErrors('brand_accent_color');
    }

    public function test_accent_color_can_be_cleared(): void
    {
        $owner = $this->ownerFor('island');
        $tenant = Tenant::query()->where('subdomain', 'island')->firstOrFail();
        $tenant->update(['brand_accent_color' => '#D0202E']);

        $this->actingAs($owner)
            ->patch('http://island.localhost/settings/branding', [
                'name' => $tenant->name,
                'currency' => $tenant->currency,
                'brand_primary_color' => null,
                'brand_accent_color' => '',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($tenant->fresh()->brand_accent_color);
    }

    public function test_owner_can_update_currency(): void
    {
        $owner = $this->ownerFor('island');
        $tenant = Tenant::query()->where('subdomain', 'island')->firstOrFail();

        $this->actingAs($owner)
            ->patch('http://island.localhost/settings/branding', [
                'name' => $tenant->name,
                'currency' => 'JMD',
                'brand_primary_color' => null,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('JMD', $tenant->fresh()->currency);
    }

    public function test_invalid_currency_fails_validation(): void
    {
        $owner = $this->ownerFor('island');

        $this->actingAs($owner)
            ->patch('http://island.localhost/settings/branding', [
                'name' => 'Island Express',
                'currency' => 'XYZ',
                'brand_primary_color' => null,
            ])
            ->assertSessionHasErrors('currency');
    }

    public function test_invalid_hex_color_fails_validation(): void
    {
        $owner = $this->ownerFor('island');

        $this->actingAs($owner)
            ->patch('http://island.localhost/settings/branding', [
                'name' => 'Island Express',
                'currency' => 'USD',
                'brand_primary_color' => 'not-a-color',
            ])
            ->assertSessionHasErrors('brand_primary_color');
    }

    public function test_primary_color_can_be_cleared(): void
    {
        $owner = $this->ownerFor('island');
        $tenant = Tenant::query()->where('subdomain', 'island')->firstOrFail();
        $tenant->update(['brand_primary_color' => '#F5C01E']);

        $this->actingAs($owner)
            ->patch('http://island.localhost/settings/branding', [
                'name' => $tenant->name,
                'currency' => $tenant->currency,
                'brand_primary_color' => '',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($tenant->fresh()->brand_primary_color);
    }

    // -------------------------------------------------------------------------
    // Logo upload
    // -------------------------------------------------------------------------

    public function test_owner_can_upload_logo(): void
    {
        Storage::fake('public');
        $owner = $this->ownerFor('island');
        $tenant = Tenant::query()->where('subdomain', 'island')->firstOrFail();

        $file = UploadedFile::fake()->image('logo.png', 200, 200);

        $this->actingAs($owner)
            ->post('http://island.localhost/settings/branding/logo', ['logo' => $file])
            ->assertRedirect(route('branding.edit'));

        $tenant->refresh();
        $this->assertNotNull($tenant->logo_path);
        Storage::disk('public')->assertExists($tenant->logo_path);
    }

    public function test_non_image_file_rejected_on_logo_upload(): void
    {
        Storage::fake('public');
        $owner = $this->ownerFor('island');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->actingAs($owner)
            ->post('http://island.localhost/settings/branding/logo', ['logo' => $file])
            ->assertSessionHasErrors('logo');
    }

    public function test_owner_can_remove_logo(): void
    {
        Storage::fake('public');
        $owner = $this->ownerFor('island');
        $tenant = Tenant::query()->where('subdomain', 'island')->firstOrFail();

        // Seed a logo path
        $path = "tenants/{$tenant->id}/logo.png";
        Storage::disk('public')->put($path, 'fake-image-data');
        $tenant->update(['logo_path' => $path]);

        $this->actingAs($owner)
            ->delete('http://island.localhost/settings/branding/logo')
            ->assertRedirect(route('branding.edit'));

        $this->assertNull($tenant->fresh()->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    // -------------------------------------------------------------------------
    // Tenant isolation
    // -------------------------------------------------------------------------

    public function test_update_only_affects_the_bound_tenant(): void
    {
        $islandOwner = $this->ownerFor('island');
        $shipdTenant = Tenant::query()->where('subdomain', 'shipd')->firstOrFail();
        $originalName = $shipdTenant->name;

        $this->actingAs($islandOwner)
            ->patch('http://island.localhost/settings/branding', [
                'name' => 'Hacked Tenant Name',
                'currency' => 'USD',
                'brand_primary_color' => null,
            ]);

        // Ship'd tenant name must be unchanged
        $this->assertSame($originalName, $shipdTenant->fresh()->name);
    }
}
