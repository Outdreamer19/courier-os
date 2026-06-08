<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait CustomerProfileValidationRules
{
    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function customerProfileRules(bool $required = false): array
    {
        $presence = $required ? 'required' : 'nullable';

        return [
            'trn' => [$presence, 'string', 'max:32'],
            'phone' => [$presence, 'string', 'max:32'],
            'jamaica_address' => [$presence, 'string', 'max:500'],
            'date_of_birth' => [
                $presence,
                'date',
                'before_or_equal:'.now()->subYears(18)->toDateString(),
            ],
        ];
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function authorisedPickupPersonRules(bool $required = false): array
    {
        $presence = $required ? 'required' : 'nullable';

        return [
            'authorised_pickup_person.full_name' => [$presence, 'string', 'max:255'],
            'authorised_pickup_person.phone' => [$presence, 'string', 'max:32'],
            'authorised_pickup_person.relationship_note' => ['nullable', 'string', 'max:255'],
            'authorised_pickup_person.id_number' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function adminRoleRules(?int $ignoreUserId = null): array
    {
        return [
            'required',
            'string',
            Rule::in(['owner', 'admin', 'staff']),
        ];
    }
}
