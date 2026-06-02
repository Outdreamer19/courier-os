<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\CustomerWarehouseAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShippingAddressController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('customer/ShippingAddress', [
            'warehouse' => CustomerWarehouseAddress::forUser($request->user()),
        ]);
    }
}
