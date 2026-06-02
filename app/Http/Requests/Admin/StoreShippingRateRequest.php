<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreShippingRateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'method' => ['required', 'string', 'max:64'],
            'currency' => ['required', 'string', 'max:8'],
            'rate_per_lb' => ['required', 'numeric', 'min:0'],
            'minimum_charge' => ['required', 'numeric', 'min:0'],
            'handling_fee' => ['nullable', 'numeric', 'min:0'],
            'min_weight_lbs' => ['nullable', 'numeric', 'min:0'],
            'max_weight_lbs' => ['nullable', 'numeric', 'min:0', 'gte:min_weight_lbs'],
            'is_active' => ['boolean'],
        ];
    }
}
