<?php

namespace App\Http\Controllers\Webhooks;

use App\Models\Tenant;
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
