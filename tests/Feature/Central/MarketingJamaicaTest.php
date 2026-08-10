<?php

namespace Tests\Feature\Central;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingJamaicaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'courieros.co']);
    }

    public function test_central_domain_renders_the_jamaica_landing_page(): void
    {
        $this->get('http://courieros.co/jamaica')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('central/Jamaica')
                ->has('pricing')
            );
    }

    public function test_tenant_subdomain_does_not_expose_the_jamaica_landing_page(): void
    {
        \App\Models\Tenant::factory()->create([
            'subdomain' => 'acme',
        ]);

        $this->get('http://acme.courieros.co/jamaica')->assertNotFound();
    }
}
