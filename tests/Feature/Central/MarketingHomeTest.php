<?php

namespace Tests\Feature\Central;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingHomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'courieros.co']);
    }

    public function test_central_domain_renders_the_marketing_home_page(): void
    {
        $this->get('http://courieros.co/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('central/Home')
                ->has('pricing')
            );
    }

    public function test_www_reserved_subdomain_also_renders_the_marketing_home_page(): void
    {
        $this->get('http://www.courieros.co/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('central/Home'));
    }

    public function test_tenant_subdomain_still_renders_the_tenant_home_page(): void
    {
        Tenant::factory()->create([
            'subdomain' => 'acme',
        ]);

        $this->get('http://acme.courieros.co/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('public/Home'));
    }
}
