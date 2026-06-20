<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\WarehouseAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'address_line_1',
    'address_line_2',
    'city',
    'state',
    'zip',
    'phone',
    'instructions',
    'is_active',
])]
class WarehouseAddress extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<WarehouseAddressFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function active(): ?self
    {
        return static::query()->active()->latest('updated_at')->first();
    }

    public function singleLine(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->zip,
        ])
            ->filter()
            ->implode(', ');
    }
}
