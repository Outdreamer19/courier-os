<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_profile_id',
    'full_name',
    'phone',
    'relationship_note',
    'id_number',
])]
class AuthorisedPickupPerson extends Model
{
    public const MAX_PER_CUSTOMER = 5;

    public function customerProfile(): BelongsTo
    {
        return $this->belongsTo(CustomerProfile::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSummaryArray(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'relationship_note' => $this->relationship_note,
            'id_number' => $this->id_number,
        ];
    }
}
