<?php

namespace App\Http\Controllers\Webhooks;

use App\Models\Tenant;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Extends Cashier's webhook handling to drive the tenant lifecycle:
 * checkout completion activates a pending tenant, subscription deletion
 * suspends it, and recovered payments reactivate it.
 */
class StripeWebhookController extends CashierController
{
    /**
     * Confirm a finished Checkout straight from Stripe, without waiting for
     * the webhook, and apply the same changes the webhooks would.
     *
     * Called from the Checkout success page. The webhook still arrives later
     * and is harmless (both handlers are idempotent), but the owner no longer
     * lands on a "finish payment" screen just because it has not arrived yet,
     * which is always the case locally where Stripe cannot reach the app.
     *
     * Only acts on a session that belongs to this tenant and is fully paid.
     */
    public function confirmCheckout(Tenant $tenant, string $sessionId): bool
    {
        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, [
            'expand' => ['subscription'],
        ]);

        $belongsToTenant = (string) data_get($session, 'metadata.tenant_id') === (string) $tenant->id;
        $paid = $session->status === 'complete' && $session->payment_status === 'paid';

        if (! $belongsToTenant || ! $paid) {
            return false;
        }

        if ($session->subscription && is_object($session->subscription)) {
            $this->handleCustomerSubscriptionCreated([
                'data' => ['object' => $session->subscription->toArray()],
            ]);
        }

        $this->handleCheckoutSessionCompleted([
            'data' => ['object' => $session->toArray()],
        ]);

        return true;
    }

    /**
     * Pick up a subscription that is already live on Stripe for this tenant
     * but never reached the app (missed webhook). Stops a paid owner from
     * being offered Checkout, and charged, a second time.
     */
    public function syncExistingSubscription(Tenant $tenant): bool
    {
        if (! $tenant->stripe_id) {
            return false;
        }

        $subscriptions = Cashier::stripe()->subscriptions->all([
            'customer' => $tenant->stripe_id,
            'status' => 'active',
            'limit' => 1,
        ]);

        $subscription = $subscriptions->data[0] ?? null;

        if (! $subscription) {
            return false;
        }

        $this->handleCustomerSubscriptionCreated([
            'data' => ['object' => $subscription->toArray()],
        ]);

        if ($tenant->isPending()) {
            $tenant->forceFill(['status' => Tenant::STATUS_ACTIVE])->save();
        }

        return true;
    }

    /**
     * Activate the tenant once Checkout completes successfully.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleCheckoutSessionCompleted(array $payload): Response
    {
        $tenant = $this->resolveTenant($payload);

        if ($tenant && $tenant->isPending()) {
            $tenant->forceFill(['status' => Tenant::STATUS_ACTIVE])->save();
        }

        return $this->successMethod();
    }

    /**
     * Suspend the tenant when its subscription is cancelled/deleted.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        parent::handleCustomerSubscriptionDeleted($payload);

        $tenant = $this->resolveTenant($payload);

        if ($tenant && ! $tenant->isCancelled()) {
            $tenant->forceFill(['status' => Tenant::STATUS_SUSPENDED])->save();
        }

        return $this->successMethod();
    }

    /**
     * Keep tenant status in sync with subscription updates (e.g. recovery
     * from past_due reactivates a suspended tenant).
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        parent::handleCustomerSubscriptionUpdated($payload);

        $tenant = $this->resolveTenant($payload);
        $status = (string) data_get($payload, 'data.object.status');

        if ($tenant && in_array($status, ['active', 'trialing'], true) && $tenant->isSuspended()) {
            $tenant->forceFill(['status' => Tenant::STATUS_ACTIVE])->save();
        }

        return $this->successMethod();
    }

    /**
     * Resolve the tenant from the webhook payload, preferring the explicit
     * metadata tenant_id and falling back to the Stripe customer id.
     *
     * @param  array<string, mixed>  $payload
     */
    private function resolveTenant(array $payload): ?Tenant
    {
        $tenantId = data_get($payload, 'data.object.metadata.tenant_id');

        if ($tenantId && $tenant = Tenant::query()->find($tenantId)) {
            return $tenant;
        }

        $customerId = data_get($payload, 'data.object.customer');

        if ($customerId) {
            return Tenant::query()->where('stripe_id', $customerId)->first();
        }

        return null;
    }
}
