<?php

namespace App\Http\Requests\Admin;

use App\Enums\Carrier;
use App\Enums\PackageStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pre_alert_id' => ['nullable', 'exists:pre_alerts,id'],
            'tracking_number' => ['nullable', 'string', 'max:128'],
            'merchant_name' => ['nullable', 'string', 'max:255'],
            'carrier' => ['nullable', Rule::enum(Carrier::class)],
            'weight_lbs' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'amount_due' => ['nullable', 'numeric', 'min:0'],
            'auto_calculate_amount' => ['boolean'],
            'status' => ['required', Rule::enum(PackageStatus::class)],
            'payment_status' => ['required', Rule::enum(PaymentStatus::class)],
            'payment_method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'payment_notes' => ['nullable', 'string', 'max:2000'],
            'customer_visible_notes' => ['nullable', 'string', 'max:5000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
