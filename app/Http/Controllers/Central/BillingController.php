<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    /**
     * Start Stripe Checkout for a freshly created (pending) tenant.
     *
     * Combines the one-time setup fee and the recurring monthly subscription
     * into a single Checkout session. On success Stripe redirects back to the
     * success route and the webhook activates the tenant.
     *
     * Checkout-session creation can fail for reasons entirely outside the
     * visitor's control — most commonly Stripe not being configured yet
     * (empty API key / price IDs). Previously that exception went unhandled
     * and Laravel rendered its raw debug page straight to the visitor: full
     * stack trace, file paths, framework version. That's a hard stop for a
     * stranger and a information leak besides. We catch it here, log it for
     * us to act on, and show a branded "we'll be in touch" page instead —
     * this does NOT fix Stripe being unconfigured, it just fails politely
     * until it is.
     */
    public function checkout(Request $request, Tenant $tenant): RedirectResponse|Response
    {
        abort_unless($tenant->isPending(), 403, 'This account has already been set up.');

        $monthlyPrice = (string) config('services.stripe.price_monthly');
        $setupPrice = (string) config('services.stripe.price_setup');

        try {
            $checkout = $tenant->newSubscription('default', $monthlyPrice)
                ->checkout([
                    'success_url' => $this->successUrl($tenant),
                    'cancel_url' => $this->cancelUrl(),
                    'line_items' => [
                        ['price' => $setupPrice, 'quantity' => 1],
                    ],
                    'metadata' => [
                        'tenant_id' => $tenant->id,
                        'tenant_subdomain' => $tenant->subdomain,
                    ],
                ]);
        } catch (\Throwable $e) {
            report($e);

            return Inertia::render('central/BillingUnavailable', [
                'tenant' => [
                    'name' => $tenant->name,
                ],
            ]);
        }

        return redirect($checkout->url);
    }

    public function success(Request $request, Tenant $tenant): Response
    {
        return Inertia::render('central/BillingSuccess', [
            'tenant' => [
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'portal_url' => $this->tenantUrl($tenant),
            ],
        ]);
    }

    public function cancel(): Response
    {
        return Inertia::render('central/BillingCancelled');
    }

    private function successUrl(Tenant $tenant): string
    {
        return route('central.billing.success', ['tenant' => $tenant->id]).'&session_id={CHECKOUT_SESSION_ID}';
    }

    private function cancelUrl(): string
    {
        return route('central.billing.cancel');
    }

    private function tenantUrl(Tenant $tenant): string
    {
        $scheme = request()->getScheme();
        $central = config('courieros.central_domain');

        return "{$scheme}://{$tenant->subdomain}.{$central}";
    }
}
