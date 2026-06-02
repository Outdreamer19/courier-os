<?php

namespace App\Http\Controllers;

use App\Support\ShippingRatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RateCalculatorController extends Controller
{
    public function __invoke(Request $request, ShippingRatePresenter $rates): JsonResponse
    {
        $weight = (float) $request->input('weight_lbs', 0);

        if ($weight <= 0) {
            return response()->json([
                'amount' => 0,
                'currency' => config('shipdjm.currency'),
                'rate' => null,
            ]);
        }

        return response()->json($rates->estimate($weight));
    }
}
