<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ShippingRateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'method',
    'currency',
    'rate_per_lb',
    'minimum_charge',
    'handling_fee',
    'min_weight_lbs',
    'max_weight_lbs',
    'is_active',
])]
class ShippingRate extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ShippingRateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rate_per_lb' => 'decimal:2',
            'minimum_charge' => 'decimal:2',
            'handling_fee' => 'decimal:2',
            'min_weight_lbs' => 'decimal:2',
            'max_weight_lbs' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Pick the active rate that matches a weight, falling back to any active rate.
     */
    public static function forWeight(?float $weightLbs = null): ?self
    {
        $query = static::query()->active();

        if ($weightLbs !== null) {
            $query->where(function (Builder $inner) use ($weightLbs) {
                $inner->whereNull('min_weight_lbs')
                    ->orWhere('min_weight_lbs', '<=', $weightLbs);
            })->where(function (Builder $inner) use ($weightLbs) {
                $inner->whereNull('max_weight_lbs')
                    ->orWhere('max_weight_lbs', '>=', $weightLbs);
            });
        }

        return $query->orderByDesc('updated_at')->first()
            ?? static::query()->active()->orderByDesc('updated_at')->first();
    }
}
