<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorisedPickupPersonRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->boolean('remove')) {
            return ['remove' => ['required', 'boolean']];
        }

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'relationship_note' => ['nullable', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:64'],
        ];
    }
}
