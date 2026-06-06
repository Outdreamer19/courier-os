<?php

namespace Tests\Feature\InvoiceFeed;

use App\Enums\BillingStatus;
use App\Enums\PackageStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\CustomerProfile;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PackageBillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'invoicefeed.enabled' => true,
            'invoicefeed.api_url' => 'https://invoicefeed.test/api/v1',
            'invoicefeed.api_token' => 'test-token',
            'invoicefeed.webhook_secret' => 'webhook-secret',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function customer(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        CustomerProfile::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function packageForCustomer(?User $customer = null): Package
    {
        $customer ??= $this->customer();

        return Package::factory()->create([
            'user_id' => $customer->id,
            'amount_due' => 2500,
            'weight_lbs' => 5,
            'billing_status' => BillingStatus::NotInvoiced,
            'payment_status' => PaymentStatus::Unpaid,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $server
     */
    private function postSignedInvoiceFeedWebhook(array $payload, array $server = []): TestResponse
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $body, (string) config('invoicefeed.webhook_secret'));

        return $this->call(
            'POST',
            route('webhooks.invoicefeed'),
            [],
            [],
            [],
            array_merge([
                'HTTP_X_INVOICEFEED_EVENT' => 'invoice.paid',
                'HTTP_X_INVOICEFEED_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ], $server),
            $body,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function invoicePaidPayload(Package $package): array
    {
        return [
            'event' => 'invoice.paid',
            'invoice_id' => 456,
            'invoice_number' => 'INV-000456',
            'external_reference' => sprintf('SHIPDJM-PKG-%06d', $package->id),
            'status' => 'paid',
            'paid_at' => '2026-06-06T14:30:00Z',
            'amount_paid' => 7000,
            'currency' => 'JMD',
        ];
    }

    public function test_generate_invoice_stores_invoicefeed_data(): void
    {
        $package = $this->packageForCustomer();

        Http::fake([
            'invoicefeed.test/api/v1/invoices' => Http::response([
                'id' => 'inv_123',
                'invoice_number' => 'IF-1001',
                'invoice_url' => 'https://invoicefeed.test/invoices/inv_123',
                'public_invoice_url' => 'https://invoicefeed.test/public/inv_123',
                'status' => 'draft',
            ], 201),
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.packages.billing.generate-invoice', ['package' => $package]))
            ->assertRedirect(route('admin.packages.edit', ['package' => $package]));

        $package->refresh();

        $this->assertSame('inv_123', $package->invoicefeed_invoice_id);
        $this->assertSame('IF-1001', $package->invoicefeed_invoice_number);
        $this->assertSame(
            'https://invoicefeed.test/public/inv_123',
            $package->invoicefeed_public_invoice_url,
        );
        $this->assertSame(BillingStatus::InvoiceCreated, $package->billing_status);
        $this->assertNotNull($package->invoicefeed_synced_at);
    }

    public function test_duplicate_generate_invoice_does_not_create_duplicate_billing_record(): void
    {
        $package = $this->packageForCustomer();
        $package->update([
            'invoicefeed_invoice_id' => 'inv_existing',
            'invoicefeed_invoice_number' => 'IF-999',
            'billing_status' => BillingStatus::InvoiceCreated,
        ]);

        Http::fake();

        $this->actingAs($this->admin())
            ->post(route('admin.packages.billing.generate-invoice', ['package' => $package]))
            ->assertRedirect(route('admin.packages.edit', ['package' => $package]));

        Http::assertNothingSent();

        $package->refresh();

        $this->assertSame('inv_existing', $package->invoicefeed_invoice_id);
        $this->assertSame('IF-999', $package->invoicefeed_invoice_number);
    }

    public function test_send_invoice_updates_billing_status(): void
    {
        $package = $this->packageForCustomer();
        $package->update([
            'invoicefeed_invoice_id' => 'inv_123',
            'billing_status' => BillingStatus::InvoiceCreated,
        ]);

        Http::fake([
            'invoicefeed.test/api/v1/invoices/inv_123/send' => Http::response([
                'status' => 'payment_pending',
            ], 200),
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.packages.billing.send-invoice', ['package' => $package]))
            ->assertRedirect(route('admin.packages.edit', ['package' => $package]));

        $package->refresh();

        $this->assertSame(BillingStatus::PaymentPending, $package->billing_status);
        $this->assertSame('payment_pending', $package->invoicefeed_status);
    }

    public function test_payment_link_is_stored(): void
    {
        $package = $this->packageForCustomer();
        $package->update([
            'invoicefeed_invoice_id' => 'inv_123',
            'billing_status' => BillingStatus::InvoiceSent,
        ]);

        Http::fake([
            'invoicefeed.test/api/v1/invoices/inv_123/payment-link' => Http::response([
                'payment_url' => 'https://invoicefeed.test/pay/inv_123',
                'status' => 'payment_pending',
            ], 200),
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.packages.billing.payment-link', ['package' => $package]))
            ->assertRedirect(route('admin.packages.edit', ['package' => $package]));

        $package->refresh();

        $this->assertSame(
            'https://invoicefeed.test/pay/inv_123',
            $package->invoicefeed_payment_url,
        );
        $this->assertSame(BillingStatus::PaymentPending, $package->billing_status);
    }

    public function test_sync_payment_status_marks_package_as_paid(): void
    {
        $package = $this->packageForCustomer();
        $package->update([
            'invoicefeed_invoice_id' => 'inv_123',
            'billing_status' => BillingStatus::PaymentPending,
            'status' => PackageStatus::ArrivedInJamaica,
        ]);

        Http::fake([
            'invoicefeed.test/api/v1/invoices/inv_123' => Http::response([
                'status' => 'paid',
            ], 200),
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.packages.billing.sync-payment', ['package' => $package]))
            ->assertRedirect(route('admin.packages.edit', ['package' => $package]));

        $package->refresh();

        $this->assertSame(BillingStatus::Paid, $package->billing_status);
        $this->assertSame('paid', $package->invoicefeed_status);
        $this->assertSame(PaymentStatus::Paid, $package->payment_status);
        $this->assertSame(PaymentMethod::Online, $package->payment_method);
        $this->assertNotNull($package->paid_at);
        $this->assertSame(PackageStatus::ReadyForPickup, $package->status);
    }

    public function test_sync_command_marks_outstanding_packages_as_paid(): void
    {
        $package = $this->packageForCustomer();
        $package->update([
            'invoicefeed_invoice_id' => 'inv_456',
            'billing_status' => BillingStatus::InvoiceSent,
        ]);

        Http::fake([
            'invoicefeed.test/api/v1/invoices/inv_456' => Http::response([
                'status' => 'paid',
            ], 200),
        ]);

        $this->artisan('shipdjm:sync-invoicefeed-payments')
            ->assertSuccessful();

        $package->refresh();

        $this->assertSame(BillingStatus::Paid, $package->billing_status);
        $this->assertSame(PaymentStatus::Paid, $package->payment_status);
    }

    public function test_failed_api_response_is_handled_safely(): void
    {
        $package = $this->packageForCustomer();

        Http::fake([
            'invoicefeed.test/api/v1/invoices' => Http::response([
                'message' => 'Service unavailable',
            ], 503),
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.packages.billing.generate-invoice', ['package' => $package]))
            ->assertRedirect(route('admin.packages.edit', ['package' => $package]));

        $package->refresh();

        $this->assertNull($package->invoicefeed_invoice_id);
        $this->assertSame(BillingStatus::Failed, $package->billing_status);
    }

    public function test_webhook_marks_package_paid_with_hmac_signature(): void
    {
        $package = $this->packageForCustomer();
        $package->update([
            'invoicefeed_invoice_id' => '456',
            'billing_status' => BillingStatus::PaymentPending,
            'status' => PackageStatus::ArrivedInJamaica,
        ]);

        $this->postSignedInvoiceFeedWebhook($this->invoicePaidPayload($package))
            ->assertOk()
            ->assertJson([
                'message' => 'Webhook processed.',
                'package_id' => $package->id,
            ]);

        $package->refresh();

        $this->assertSame(BillingStatus::Paid, $package->billing_status);
        $this->assertSame('paid', $package->invoicefeed_status);
        $this->assertSame(PaymentStatus::Paid, $package->payment_status);
        $this->assertSame(PaymentMethod::Online, $package->payment_method);
        $this->assertSame('INV-000456', $package->invoicefeed_invoice_number);
        $this->assertNotNull($package->invoicefeed_synced_at);
        $this->assertNotNull($package->paid_at);
        $this->assertSame(PackageStatus::ReadyForPickup, $package->status);
    }

    public function test_webhook_rejects_invalid_hmac_signature(): void
    {
        $package = $this->packageForCustomer();
        $body = json_encode($this->invoicePaidPayload($package), JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            route('webhooks.invoicefeed'),
            [],
            [],
            [],
            [
                'HTTP_X_INVOICEFEED_EVENT' => 'invoice.paid',
                'HTTP_X_INVOICEFEED_SIGNATURE' => 'invalid-signature',
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            $body,
        )->assertUnauthorized();

        $package->refresh();

        $this->assertSame(BillingStatus::NotInvoiced, $package->billing_status);
        $this->assertSame(PaymentStatus::Unpaid, $package->payment_status);
    }

    public function test_webhook_rejects_unsupported_event(): void
    {
        $package = $this->packageForCustomer();
        $payload = [
            ...$this->invoicePaidPayload($package),
            'event' => 'invoice.sent',
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $body, (string) config('invoicefeed.webhook_secret'));

        $this->call(
            'POST',
            route('webhooks.invoicefeed'),
            [],
            [],
            [],
            [
                'HTTP_X_INVOICEFEED_EVENT' => 'invoice.sent',
                'HTTP_X_INVOICEFEED_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            $body,
        )->assertUnprocessable();

        $package->refresh();

        $this->assertSame(PaymentStatus::Unpaid, $package->payment_status);
    }

    public function test_webhook_is_idempotent_when_package_already_paid(): void
    {
        $package = $this->packageForCustomer();
        $paidAt = now()->subDay();
        $package->update([
            'invoicefeed_invoice_id' => '456',
            'billing_status' => BillingStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'payment_method' => PaymentMethod::Online,
            'paid_at' => $paidAt,
            'status' => PackageStatus::ReadyForPickup,
            'ready_for_pickup_at' => now()->subDay(),
        ]);

        $this->postSignedInvoiceFeedWebhook($this->invoicePaidPayload($package))
            ->assertOk();

        $package->refresh();

        $this->assertSame(BillingStatus::Paid, $package->billing_status);
        $this->assertSame(PaymentStatus::Paid, $package->payment_status);
        $this->assertSame($paidAt->getTimestamp(), $package->paid_at?->getTimestamp());
        $this->assertSame(PackageStatus::ReadyForPickup, $package->status);
    }

    public function test_webhook_marks_package_paid_by_external_reference_using_legacy_secret(): void
    {
        $package = $this->packageForCustomer();
        $package->update([
            'invoicefeed_invoice_id' => 'inv_webhook',
            'billing_status' => BillingStatus::PaymentPending,
        ]);

        $this->postJson(route('webhooks.invoicefeed'), [
            'external_reference' => 'SHIPDJM-PKG-'.$package->id,
            'status' => 'paid',
        ], [
            'X-INVOICEFEED-SECRET' => 'webhook-secret',
        ])->assertOk();

        $package->refresh();

        $this->assertSame(BillingStatus::Paid, $package->billing_status);
        $this->assertSame(PaymentStatus::Paid, $package->payment_status);
    }

    public function test_webhook_does_not_move_package_to_ready_for_pickup_from_unrelated_status(): void
    {
        $package = $this->packageForCustomer();
        $package->update([
            'invoicefeed_invoice_id' => '456',
            'billing_status' => BillingStatus::PaymentPending,
            'status' => PackageStatus::InTransitToJamaica,
        ]);

        $this->postSignedInvoiceFeedWebhook($this->invoicePaidPayload($package))
            ->assertOk();

        $package->refresh();

        $this->assertSame(PaymentStatus::Paid, $package->payment_status);
        $this->assertSame(PackageStatus::InTransitToJamaica, $package->status);
    }

    public function test_customer_package_show_includes_payment_link(): void
    {
        $customer = $this->customer();
        $package = Package::factory()->create([
            'user_id' => $customer->id,
            'amount_due' => 1800,
            'invoicefeed_public_invoice_url' => 'https://invoicefeed.test/public/inv_789',
            'invoicefeed_payment_url' => 'https://invoicefeed.test/pay/inv_789',
            'billing_status' => BillingStatus::PaymentPending,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $this->actingAs($customer)
            ->get(route('portal.packages.show', ['package' => $package]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('customer/packages/Show')
                ->where('package.payment_url', 'https://invoicefeed.test/pay/inv_789')
                ->where('package.can_pay_online', true)
                ->where('package.invoice_url', 'https://invoicefeed.test/public/inv_789'));
    }
}
