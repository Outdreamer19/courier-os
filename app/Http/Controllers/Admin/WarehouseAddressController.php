<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWarehouseAddressRequest;
use App\Models\WarehouseAddress;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseAddressController extends Controller
{
    public function index(): Response
    {
        $addresses = WarehouseAddress::query()
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (WarehouseAddress $address) => [
                'id' => $address->id,
                'name' => $address->name,
                'city' => $address->city,
                'state' => $address->state,
                'is_active' => $address->is_active,
                'single_line' => $address->singleLine(),
            ]);

        return Inertia::render('admin/warehouse/Index', [
            'addresses' => $addresses,
        ]);
    }

    public function edit(WarehouseAddress $warehouse): Response
    {
        return Inertia::render('admin/warehouse/Edit', [
            'warehouse' => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'address_line_1' => $warehouse->address_line_1,
                'address_line_2' => $warehouse->address_line_2,
                'city' => $warehouse->city,
                'state' => $warehouse->state,
                'zip' => $warehouse->zip,
                'phone' => $warehouse->phone,
                'instructions' => $warehouse->instructions,
                'is_active' => $warehouse->is_active,
            ],
        ]);
    }

    public function update(
        UpdateWarehouseAddressRequest $request,
        WarehouseAddress $warehouse,
    ): RedirectResponse {
        $warehouse->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($warehouse->is_active) {
            WarehouseAddress::query()
                ->whereKeyNot($warehouse->id)
                ->update(['is_active' => false]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Warehouse address updated.']);

        return to_route('admin.warehouse.index');
    }
}
