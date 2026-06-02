<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingRateRequest;
use App\Http\Requests\Admin\UpdateShippingRateRequest;
use App\Models\ShippingRate;
use App\Support\ShippingRatePresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShippingRateController extends Controller
{
    public function __construct(private readonly ShippingRatePresenter $presenter) {}

    public function index(): Response
    {
        $rates = ShippingRate::query()
            ->orderByRaw('COALESCE(min_weight_lbs, 0) ASC')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (ShippingRate $rate) => $this->presenter->formatRate($rate));

        return Inertia::render('admin/shipping-rates/Index', [
            'rates' => $rates,
            'currency' => config('shipdjm.currency'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/shipping-rates/Create', [
            'currency' => config('shipdjm.currency'),
        ]);
    }

    public function store(StoreShippingRateRequest $request): RedirectResponse
    {
        ShippingRate::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Shipping rate created.']);

        return to_route('admin.shipping-rates.index');
    }

    public function edit(ShippingRate $shippingRate): Response
    {
        return Inertia::render('admin/shipping-rates/Edit', [
            'rate' => $this->presenter->formatRate($shippingRate),
        ]);
    }

    public function update(
        UpdateShippingRateRequest $request,
        ShippingRate $shippingRate,
    ): RedirectResponse {
        $shippingRate->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Shipping rate updated.']);

        return to_route('admin.shipping-rates.index');
    }
}
