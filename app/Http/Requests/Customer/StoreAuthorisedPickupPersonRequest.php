<?php

namespace App\Http\Requests\Customer;

use App\Models\AuthorisedPickupPerson;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorisedPickupPersonRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'relationship_note' => ['nullable', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $profile = $this->user()?->customerProfile;

            if (
                $profile
                && $profile->authorisedPickupPeople()->count() >= AuthorisedPickupPerson::MAX_PER_CUSTOMER
            ) {
                $validator->errors()->add(
                    'full_name',
                    'You can only add up to '.AuthorisedPickupPerson::MAX_PER_CUSTOMER.' authorised pickup people.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Please enter the pickup person\'s full name.',
            'phone.required' => 'Please enter a phone number for the pickup person.',
        ];
    }
}
