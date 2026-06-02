<?php

namespace App\Support;

use App\Models\User;
use App\Models\WarehouseAddress;

/**
 * Builds the customer-facing Florida shipping address block, including the
 * customer's name and suite/reference line for checkout.
 */
class CustomerWarehouseAddress
{
    /**
     * @return array<string, mixed>|null
     */
    public static function forUser(User $user, ?WarehouseAddress $warehouse = null): ?array
    {
        $warehouse ??= WarehouseAddress::active();

        if (! $warehouse) {
            return null;
        }

        $reference = $user->customerReference() ?? '—';

        $fullLines = collect([
            $user->name,
            "Suite {$reference}",
            $warehouse->address_line_1,
            $warehouse->address_line_2,
            "{$warehouse->city}, {$warehouse->state} {$warehouse->zip}",
            $warehouse->phone ? "Phone: {$warehouse->phone}" : null,
        ])->filter()->values();

        return [
            'name' => $warehouse->name,
            'customer_name' => $user->name,
            'customer_reference' => $reference,
            'address_line_1' => $warehouse->address_line_1,
            'address_line_2' => $warehouse->address_line_2,
            'city' => $warehouse->city,
            'state' => $warehouse->state,
            'zip' => $warehouse->zip,
            'phone' => $warehouse->phone,
            'instructions' => $warehouse->instructions,
            'single_line' => $warehouse->singleLine(),
            'full_address' => $fullLines->implode("\n"),
        ];
    }
}
