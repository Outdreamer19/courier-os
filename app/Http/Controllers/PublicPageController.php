<?php

namespace App\Http\Controllers;

use App\Support\ShippingRatePresenter;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function __construct(private readonly ShippingRatePresenter $rates) {}

    public function home(): Response
    {
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
