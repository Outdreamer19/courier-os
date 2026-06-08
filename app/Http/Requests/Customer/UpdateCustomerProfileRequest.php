<?php

namespace App\Http\Requests\Customer;

use App\Concerns\CustomerProfileValidationRules;
use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerProfileRequest extends FormRequest
{
    use CustomerProfileValidationRules, ProfileValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()->id),
            ...$this->customerProfileRules(required: true),
            'whatsapp_number' => ['nullable', 'string', 'max:32'],
            'parish' => ['nullable', 'string', 'max:96'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old.',
        ];
    }
}
