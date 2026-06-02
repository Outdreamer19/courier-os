<?php

namespace App\Http\Requests\Customer;

use App\Enums\Carrier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreAlertRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $mimes = config('shipdjm.invoice_uploads.allowed_mimes', ['pdf', 'jpg', 'jpeg', 'png', 'webp']);
        $maxKb = (int) config('shipdjm.invoice_uploads.max_kilobytes', 8192);

        return [
            'merchant_name' => ['required', 'string', 'max:255'],
            'order_number' => ['nullable', 'string', 'max:128'],
            'tracking_number' => ['nullable', 'string', 'max:128'],
            'carrier' => ['required', Rule::enum(Carrier::class)],
            'expected_delivery_date' => ['nullable', 'date'],
            'item_description' => ['required', 'string', 'max:2000'],
            'declared_value' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'invoice' => [
                'nullable',
                'file',
                'max:'.$maxKb,
                'mimes:'.implode(',', $mimes),
            ],
        ];
    }
}
