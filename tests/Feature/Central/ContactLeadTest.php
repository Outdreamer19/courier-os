<?php

namespace Tests\Feature\Central;

use App\Models\PlatformLead;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PlatformLeadReceived;
use App\Support\Tenancy\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactLeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['courieros.central_domain' => 'courieros.co']);
    }

    /**
     * @return array<string, mixed>
     */
    private function brief(array $overrides = []): array
    {
        return [
            'stage' => 'manual',
            'business_name' => 'Island Express',
            'country' => 'jamaica',
            'monthly_volume' => '500_2000',
            'forwarder' => 'partner',
            'needs' => ['portal', 'tracking', 'migration'],
            'timeline' => 'now',
            'plan_interest' => 'done_for_you',
            'challenge' => 'Customers keep asking where their package is.',
            'name' => 'Marsha Brown',
            'email' => 'marsha@example.com',
            'phone' => '+1 876 555 0123',
            'preferred_channel' => 'whatsapp',
            'best_time' => 'morning',
            'utm_campaign' => 'jm-launch',
            ...$overrides,
        ];
    }

    private function platformOwner(): User
    {
        app(TenantManager::class)->forget();

        return User::factory()->create([
            'role' => User::ROLE_PLATFORM_OWNER,
            'tenant_id' => null,
        ]);
    }

    public function test_central_domain_renders_the_three_step_contact_page(): void
    {
        $this->get('http://courieros.co/contact')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('central/Contact')
                ->has('options.stages', 4)
                ->has('options.needs')
                ->has('sales')
            );
    }

    public function test_tenant_sites_keep_their_own_contact_page(): void
    {
        Tenant::factory()->create(['subdomain' => 'acme']);

        $this->get('http://acme.courieros.co/contact')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('public/Contact'));
    }

    public function test_a_prospect_can_send_a_brief_and_it_is_qualified(): void
    {
        Notification::fake();
        $owner = $this->platformOwner();

        $this->post('http://courieros.co/contact/enquiry', $this->brief())
            ->assertRedirect('/contact')
            ->assertSessionHas('success');

        $lead = PlatformLead::sole();

        $this->assertSame(PlatformLead::STATUS_NEW, $lead->status);
        $this->assertSame(PlatformLead::PRIORITY_HOT, $lead->priority);
        $this->assertSame('enterprise', $lead->recommended_plan);
        $this->assertSame(['portal', 'tracking', 'migration'], $lead->needs);
        $this->assertSame('jm-launch', $lead->utm_campaign);
        $this->assertNotEmpty($lead->talking_points);

        Notification::assertSentTo($owner, PlatformLeadReceived::class);
    }

    public function test_an_early_researcher_is_scored_cold(): void
    {
        Notification::fake();

        $this->post('http://courieros.co/contact/enquiry', $this->brief([
            'stage' => 'starting',
            'monthly_volume' => 'none',
            'forwarder' => 'need',
            'timeline' => 'exploring',
            'plan_interest' => 'unsure',
            'needs' => ['website'],
        ]))->assertRedirect('/contact');

        $lead = PlatformLead::sole();

        $this->assertSame(PlatformLead::PRIORITY_COLD, $lead->priority);
        $this->assertSame('starter', $lead->recommended_plan);
        $this->assertTrue(collect($lead->talking_points)->contains(
            fn (string $p) => str_contains($p, 'forwarding partner')
        ));
    }

    public function test_whatsapp_contact_requires_a_phone_number(): void
    {
        $this->post('http://courieros.co/contact/enquiry', $this->brief(['phone' => '']))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('platform_leads', 0);
    }

    public function test_email_contact_does_not_need_a_phone_number(): void
    {
        Notification::fake();

        $this->post('http://courieros.co/contact/enquiry', $this->brief([
            'phone' => '',
            'preferred_channel' => 'email',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('platform_leads', 1);
    }

    public function test_unknown_option_keys_are_rejected(): void
    {
        $this->post('http://courieros.co/contact/enquiry', $this->brief([
            'stage' => 'pirate',
            'needs' => ['teleporter'],
        ]))->assertSessionHasErrors(['stage', 'needs.0']);
    }

    public function test_honeypot_submissions_look_successful_but_are_not_stored(): void
    {
        Notification::fake();

        $this->post('http://courieros.co/contact/enquiry', $this->brief(['website' => 'http://spam.test']))
            ->assertRedirect('/contact')
            ->assertSessionHas('success');

        $this->assertDatabaseCount('platform_leads', 0);
        Notification::assertNothingSent();
    }

    public function test_the_enquiry_endpoint_is_not_available_on_tenant_sites(): void
    {
        Tenant::factory()->create(['subdomain' => 'acme']);

        $this->post('http://acme.courieros.co/contact/enquiry', $this->brief())
            ->assertNotFound();

        $this->assertDatabaseCount('platform_leads', 0);
    }

    public function test_platform_owner_can_review_and_update_leads(): void
    {
        Notification::fake();
        $this->post('http://courieros.co/contact/enquiry', $this->brief());
        $lead = PlatformLead::sole();
        $owner = $this->platformOwner();

        $this->actingAs($owner)
            ->get('http://courieros.co/platform/leads')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('central/admin/Leads')
                ->has('leads', 1)
                ->where('leads.0.answers.Country', 'Jamaica')
            );

        $this->actingAs($owner)
            ->patch("http://courieros.co/platform/leads/{$lead->id}", [
                'status' => 'contacted',
                'admin_notes' => 'Demo booked for Thursday.',
            ])
            ->assertRedirect();

        $lead->refresh();
        $this->assertSame('contacted', $lead->status);
        $this->assertNotNull($lead->contacted_at);
    }

    public function test_guests_cannot_see_leads(): void
    {
        $this->get('http://courieros.co/platform/leads')->assertRedirect();
    }
}
