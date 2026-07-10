<?php

namespace App\Http\Controllers;

use App\Support\ShippingRatePresenter;
use App\Support\Tenancy\TenantManager;
use Illuminate\Contracts\View\View;
use Inertia\Inertia;
use Inertia\Response;

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

    public function home(): Response|View
    {
        $tenant = $this->tenants->current();

        if ($tenant && isset(self::CUSTOM_HOME_VIEWS[$tenant->subdomain])) {
            return view(self::CUSTOM_HOME_VIEWS[$tenant->subdomain]);
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
        return Inertia::render('public/Terms');
    }

    public function privacy(): Response
    {
        return Inertia::render('public/Privacy');
    }

    public function shipping(): Response
    {
        return Inertia::render('public/ShippingPolicy');
    }

    public function refund(): Response
    {
        return Inertia::render('public/RefundPolicy');
    }

    public function restrictedItems(): Response
    {
        return Inertia::render('public/RestrictedItems');
    }
}
