<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'subdomain',
    'custom_domain',
    'status',
    'currency',
    'customer_reference_prefix',
    'package_reference_prefix',
    'whatsapp_number',
    'logo_path',
    'brand_primary_color',
    'trial_ends_at',
])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_CANCELLED = 'cancelled';

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'currency' => 'USD',
        'package_reference_prefix' => 'PKG',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Resolve a tenant by its subdomain or custom domain.
     */
    public static function resolveByHost(string $subdomain, ?string $host = null): ?self
    {
        $query = static::query();

        if ($host) {
            return $query
                ->where('subdomain', $subdomain)
                ->orWhere('custom_domain', $host)
                ->first();
        }

        return $query->where('subdomain', $subdomain)->first();
    }
}
