<?php

namespace Tests\Feature\Billing;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSignupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'courieros.co']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'Acme Courier',
            'subdomain' => 'acme',
            'currency' => 'USD',
            'customer_reference_prefix' => 'ACM',
            'owner_name' => 'Jane Owner',
            'owner_email' => 'jane@acme.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ], $overrides);
    }

    public function test_signup_page_renders_on_central_domain(): void
    {
        $this->get('http://courieros.co/signup')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('central/Signup'));
    }

    public function test_signup_creates_pending_tenant_and_owner(): void
    {
        $this->post('http://courieros.co/signup', $this->validPayload());

        $tenant = Tenant::query()->where('subdomain', 'acme')->first();
        $this->assertNotNull($tenant);
        $this->assertSame(Tenant::STATUS_PENDING, $tenant->status);
        $this->assertSame('ACM', $tenant->customer_reference_prefix);

        // Owner user is created and scoped to the tenant.
        app(TenantManager::class)->set($tenant);
        $owner = User::query()->where('email', 'jane@acme.com')->first();
        $this->assertNotNull($owner);
        $this->assertSame(User::ROLE_OWNER, $owner->role);
        $this->assertSame($tenant->id, $owner->tenant_id);
    }

    public function test_signup_redirects_toward_checkout(): void
    {
        $response = $this->post('http://courieros.co/signup', $this->validPayload());

        // Either a redirect to Stripe Checkout or to the billing start route.
        $response->assertRedirect();
    }

    public function test_subdomain_must_be_unique(): void
    {
        Tenant::factory()->create(['subdomain' => 'taken']);

        $this->from('http://courieros.co/signup')
            ->post('http://courieros.co/signup', $this->validPayload(['subdomain' => 'taken']))
            ->assertRedirect('http://courieros.co/signup')
            ->assertSessionHasErrors('subdomain');
    }

    public function test_reserved_subdomain_is_rejected(): void
    {
        $this->from('http://courieros.co/signup')
            ->post('http://courieros.co/signup', $this->validPayload(['subdomain' => 'www']))
            ->assertSessionHasErrors('subdomain');
    }

    public function test_subdomain_is_normalised_to_lowercase_slug(): void
    {
        $this->post('http://courieros.co/signup', $this->validPayload([
            'subdomain' => 'Acme Express',
        ]));

        $this->assertDatabaseHas('tenants', ['subdomain' => 'acme-express']);
    }

    public function test_invalid_payload_is_rejected(): void
    {
        $this->from('http://courieros.co/signup')
            ->post('http://courieros.co/signup', $this->validPayload([
                'owner_email' => 'not-an-email',
            ]))
            ->assertSessionHasErrors('owner_email');

        $this->assertDatabaseCount('tenants', 0);
    }
}
