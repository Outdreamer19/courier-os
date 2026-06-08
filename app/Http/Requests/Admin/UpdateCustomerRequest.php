<?php

namespace App\Http\Requests\Admin;

use App\Concerns\CustomerProfileValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    use CustomerProfileValidationRules, ProfileValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $customer */
        $customer = $this->route('customer');

        return [
            ...$this->profileRules($customer->id),
            ...$this->customerProfileRules(required: true),
            'whatsapp_number' => ['nullable', 'string', 'max:32'],
            'parish' => ['nullable', 'string', 'max:96'],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_SUSPENDED])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_of_birth.before_or_equal' => 'Customer must be at least 18 years old.',
        ];
    }
}
