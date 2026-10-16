<?php

namespace Tests\Feature\Central;

use App\Models\Tenant;
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
                ->has('sales.whatsapp')
                ->has('sales.founding.spotsLeft')
            );
    }

    public function test_share_previews_are_server_rendered_with_courieros_branding(): void
    {
        config(['app.name' => "Ship'd JM"]);

        $html = $this->get('http://courieros.co/jamaica')->assertOk()->getContent();

        // WhatsApp and Instagram crawlers never run JavaScript, so the title,
        // description and image have to be in the Blade response itself.
        $this->assertStringContainsString('property="og:image"', $html);
        $this->assertStringContainsString('courieros-jamaica-og.png', $html);
        $this->assertStringContainsString('/branding/courieros-icon.svg', $html);
        $this->assertStringNotContainsString('Ship&#039;d JM</title>', $html);
        $this->assertStringNotContainsString('today-shipping-icon.png', $html);
    }

    public function test_ad_pixels_are_only_injected_when_configured(): void
    {
        $this->get('http://courieros.co/jamaica')
            ->assertDontSee('fbevents.js', false)
            ->assertDontSee('googletagmanager.com', false);

        config([
            'courieros.marketing.meta_pixel_id' => '1234567890',
            'courieros.marketing.google_tag_id' => 'G-TEST123',
        ]);

        $this->get('http://courieros.co/jamaica')
            ->assertSee('fbevents.js', false)
            ->assertSee('googletagmanager.com/gtag/js?id=G-TEST123', false);
    }

    public function test_tenant_sites_never_get_platform_pixels_or_share_tags(): void
    {
        Tenant::factory()->create(['subdomain' => 'acme']);
        config(['courieros.marketing.meta_pixel_id' => '1234567890']);

        $this->get('http://acme.courieros.co/')
            ->assertDontSee('fbevents.js', false)
            ->assertDontSee('courieros-og.png', false);
    }

    public function test_tenant_subdomain_does_not_expose_the_jamaica_landing_page(): void
    {
        Tenant::factory()->create([
            'subdomain' => 'acme',
        ]);

        $this->get('http://acme.courieros.co/jamaica')->assertNotFound();
    }
}
