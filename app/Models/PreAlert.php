<?php

namespace App\Models;

use App\Enums\Carrier;
use App\Enums\PreAlertStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PreAlertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'merchant_name',
    'order_number',
    'tracking_number',
    'carrier',
    'expected_delivery_date',
    'item_description',
    'declared_value',
    'invoice_path',
    'status',
    'admin_notes',
    'customer_notes',
])]
class PreAlert extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<PreAlertFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'expected_delivery_date' => 'date',
            'declared_value' => 'decimal:2',
            'status' => PreAlertStatus::class,
            'carrier' => Carrier::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): HasOne
    {
        return $this->hasOne(Package::class);
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    public function isCancellable(): bool
    {
        return $this->status->isCancellable();
    }
}
