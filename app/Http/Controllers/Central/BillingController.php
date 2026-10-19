<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\Pricing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
    public function checkout(Request $request, Tenant $tenant): SymfonyResponse|Response
    {
        abort_unless($tenant->isPending(), 403, 'This account has already been set up.');

        $monthlyPrice = (string) config('services.stripe.price_monthly');
        $setupPrice = $this->setupPrice();

        try {
            $checkout = $tenant->newSubscription('default', $monthlyPrice)
                ->checkout([
                    'success_url' => $this->successUrl($tenant),
                    'cancel_url' => $this->cancelUrl($tenant),
                    'line_items' => [
                        ['price' => $setupPrice, 'quantity' => 1],
                    ],
                    // Charge in US dollars, the currency every price on the site
                    // is shown in. Adaptive Pricing otherwise converts to the
                    // buyer's local currency (GBP, JMD...) and adds a
                    // conversion fee on top.
                    'adaptive_pricing' => ['enabled' => false],
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

        // The signup form posts over Inertia's XHR and lands here via its
        // redirect. An XHR cannot follow a redirect onto checkout.stripe.com
        // (CORS), so ask Inertia for a full page visit instead.
        return Inertia::location($checkout->url);
    }

    /**
     * The founding (discounted) setup price while founding spots remain and
     * a Stripe price for it is configured; the standard setup otherwise.
     */
    private function setupPrice(): string
    {
        $founding = (string) config('services.stripe.price_setup_founding');

        if ($founding !== '' && Pricing::foundingSetup() !== null) {
            return $founding;
        }

        return (string) config('services.stripe.price_setup');
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

    public function cancel(Request $request): Response
    {
        $tenant = Tenant::query()
            ->where('subdomain', (string) $request->query('tenant'))
            ->first();

        return Inertia::render('central/BillingCancelled', [
            'finishUrl' => $tenant?->isPending()
                ? route('central.billing.finish', ['tenant' => $tenant->subdomain])
                : null,
        ]);
    }

    /**
     * "Finish setting up" page for a courier that signed up but has not paid.
     *
     * Their own site redirects here (see ResolveTenant) rather than showing a
     * bare "not currently active" error, so an owner coming back later goes
     * straight to payment. Anyone else who lands here sees the same page,
     * which only names the business and offers checkout for it.
     */
    public function finish(Tenant $tenant): SymfonyResponse|Response
    {
        if (! $tenant->isPending()) {
            return Inertia::location($tenant->url('/login'));
        }

        return Inertia::render('central/BillingFinish', [
            'tenant' => [
                'name' => $tenant->name,
                'host' => $tenant->host(),
                'checkout_url' => route('central.billing.checkout', ['tenant' => $tenant->id]),
            ],
            'pricing' => Pricing::summary(),
        ]);
    }

    private function successUrl(Tenant $tenant): string
    {
        // `?`, not `&`: the route has no query string of its own, so `&` ended
        // up inside the {tenant} path segment.
        return route('central.billing.success', ['tenant' => $tenant->id]).'?session_id={CHECKOUT_SESSION_ID}';
    }

    private function cancelUrl(Tenant $tenant): string
    {
        return route('central.billing.cancel', ['tenant' => $tenant->subdomain]);
    }

    private function tenantUrl(Tenant $tenant): string
    {
        $scheme = request()->getScheme();
        $central = config('courieros.central_domain');

        return "{$scheme}://{$tenant->subdomain}.{$central}";
    }
}
