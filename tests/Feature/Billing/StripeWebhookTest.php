<?php

namespace Tests\Feature\Billing;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable signature verification for these unit-level webhook tests.
        config(['cashier.webhook.secret' => null]);
    }

    private function postWebhook(array $payload): TestResponse
    {
        return $this->postJson('/stripe/webhook', $payload, [
            'Stripe-Signature' => 'test',
        ]);
    }

    public function test_checkout_completion_activates_a_pending_tenant(): void
    {
        $tenant = Tenant::factory()->pending()->create(['stripe_id' => 'cus_abc']);

        $response = $this->postWebhook([
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'customer' => 'cus_abc',
                    'metadata' => ['tenant_id' => $tenant->id],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertSame(Tenant::STATUS_ACTIVE, $tenant->fresh()->status);
    }

    public function test_subscription_deletion_suspends_the_tenant(): void
    {
        $tenant = Tenant::factory()->create([
            'status' => Tenant::STATUS_ACTIVE,
            'stripe_id' => 'cus_del',
        ]);

        $response = $this->postWebhook([
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'customer' => 'cus_del',
                    'id' => 'sub_123',
                    'status' => 'canceled',
                    'items' => ['data' => []],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertSame(Tenant::STATUS_SUSPENDED, $tenant->fresh()->status);
    }

    public function test_unknown_customer_is_ignored_gracefully(): void
    {
        $response = $this->postWebhook([
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'customer' => 'cus_missing',
                    'metadata' => [],
                ],
            ],
        ]);

        $response->assertOk();
    }
}
