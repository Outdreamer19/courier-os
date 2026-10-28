<?php

namespace App\Http\Controllers;

use App\Support\Demo\DemoMode;
use App\Support\Pricing;
use App\Support\ShippingRatePresenter;
use App\Support\Tenancy\TenantManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PublicPageController extends Controller
{
    public function __construct(
        private readonly ShippingRatePresenter $rates,
        private readonly TenantManager $tenants,
    ) {}

    /**
     * Tenants with a bespoke marketing page (built outside the shared
     * Inertia public site) get their subdomain mapped to a dedicated
     * Blade view here instead of the generic Home page.
     *
     * @var array<string, string>
     */
    private const CUSTOM_HOME_VIEWS = [
        'today' => 'tenants.today-shipping',
    ];

    public function home(Request $request): Response|View|SymfonyResponse
    {
        $tenant = $this->tenants->current();

        if ($tenant && isset(self::CUSTOM_HOME_VIEWS[$tenant->subdomain])) {
            // Blade HTML over an Inertia XHR is rendered inside Inertia's
            // error <dialog> (e.g. after logout → /). Force a full visit.
            if ($request->inertia()) {
                return Inertia::location(url()->current());
            }

            return view(self::CUSTOM_HOME_VIEWS[$tenant->subdomain]);
        }

        if (! $this->tenants->hasTenant()) {
            return Inertia::render('central/Home', [
                'pricing' => Pricing::summary(),
                'salesWhatsapp' => (string) config('courieros.marketing.sales_whatsapp') ?: null,
                'liveDemoUrl' => DemoMode::url(),
            ])->withViewData([
                'meta' => [
                    'title' => 'Courier software for Caribbean shop-and-ship businesses | CourierOS',
                    'description' => 'Your own branded courier website, customer portal with a Florida address, pre-alerts, package tracking, billing and WhatsApp updates. One flat monthly price.',
                ],
            ]);
        }

        return Inertia::render('public/Home', [
            'rate' => $this->rates->primaryRate(),
            'rateTiers' => $this->rates->activeTiers(),
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('public/About');
    }

    public function rates(): Response
    {
        return Inertia::render('public/Rates', [
            'rate' => $this->rates->primaryRate(),
            'rateTiers' => $this->rates->activeTiers(),
        ]);
    }

    public function terms(): Response
    {
        if (! $this->tenants->hasTenant()) {
            return $this->platformLegal('central/legal/Terms', 'Terms of Service', 'The agreement between your courier business and CourierOS: plans, billing, cancellation, data and liability.', [
                'pricing' => Pricing::summary(),
            ]);
        }

        return Inertia::render('public/Terms');
    }

    public function privacy(): Response
    {
        if (! $this->tenants->hasTenant()) {
            return $this->platformLegal('central/legal/Privacy', 'Privacy Policy', 'What data CourierOS collects, why, who helps us process it, how the Meta Pixel is used, and your rights.');
        }

        return Inertia::render('public/Privacy');
    }

    public function shipping(): Response|RedirectResponse
    {
        // Shipping and restricted-items policies belong to each courier;
        // the platform itself ships nothing.
        if (! $this->tenants->hasTenant()) {
            return redirect('/legal/terms');
        }

        return Inertia::render('public/ShippingPolicy');
    }

    public function refund(): Response
    {
        if (! $this->tenants->hasTenant()) {
            return $this->platformLegal('central/legal/Refunds', 'Refunds & Cancellation', 'Cancel CourierOS any time. How monthly fees, the setup fee and your data are handled when you do.');
        }

        return Inertia::render('public/RefundPolicy');
    }

    /**
     * CourierOS's own legal pages on the central domain, written for the
     * courier businesses that subscribe (not for their customers).
     *
     * @param  array<string, mixed>  $props
     */
    private function platformLegal(string $component, string $title, string $description, array $props = []): Response
    {
        $company = config('courieros.company');

        return Inertia::render($component, [
            ...$props,
            'company' => [
                'name' => $company['name'],
                'number' => $company['number'],
                'address' => $company['address'],
                'email' => $company['email'],
            ],
            'updated' => $company['legal_updated'],
        ])->withViewData([
            'meta' => [
                'title' => $title.' | CourierOS',
                'description' => $description,
            ],
        ]);
    }

    public function restrictedItems(): Response|RedirectResponse
    {
        if (! $this->tenants->hasTenant()) {
            return redirect('/legal/terms');
        }

        return Inertia::render('public/RestrictedItems');
    }
}
