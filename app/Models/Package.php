<?php

namespace App\Models;

use App\Enums\BillingStatus;
use App\Enums\Carrier;
use App\Enums\PackageStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'pre_alert_id',
    'package_reference',
    'tracking_number',
    'merchant_name',
    'carrier',
    'weight_lbs',
    'declared_value',
    'amount_due',
    'invoicefeed_invoice_id',
    'invoicefeed_invoice_number',
    'invoicefeed_invoice_url',
    'invoicefeed_public_invoice_url',
    'invoicefeed_payment_url',
    'invoicefeed_status',
    'invoicefeed_synced_at',
    'billing_status',
    'payment_status',
    'payment_method',
    'paid_at',
    'status',
    'received_at_warehouse_at',
    'shipped_to_jamaica_at',
    'arrived_in_jamaica_at',
    'ready_for_pickup_at',
    'picked_up_at',
    'admin_notes',
    'customer_visible_notes',
])]
class Package extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<PackageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight_lbs' => 'decimal:2',
            'declared_value' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'billing_status' => BillingStatus::class,
            'invoicefeed_synced_at' => 'datetime',
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'status' => PackageStatus::class,
            'carrier' => Carrier::class,
            'paid_at' => 'datetime',
            'received_at_warehouse_at' => 'datetime',
            'shipped_to_jamaica_at' => 'datetime',
            'arrived_in_jamaica_at' => 'datetime',
            'ready_for_pickup_at' => 'datetime',
            'picked_up_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preAlert(): BelongsTo
    {
        return $this->belongsTo(PreAlert::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PackageStatusHistory::class)->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            PackageStatus::PickedUp,
            PackageStatus::Cancelled,
        ]);
    }

    public function scopeOutstandingPayment(Builder $query): Builder
    {
        return $query->whereIn('payment_status', [
            PaymentStatus::Unpaid,
            PaymentStatus::Pending,
        ]);
    }
}
