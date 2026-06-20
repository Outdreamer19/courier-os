<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes a model to the currently resolved tenant.
 *
 * When a tenant is bound in the TenantManager, all queries are filtered
 * by tenant_id and newly created models have their tenant_id auto-filled.
 * When no tenant is bound (central/platform context), the scope is a
 * no-op so platform-level code can see across all tenants.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $manager = app(TenantManager::class);

            if ($manager->hasTenant()) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $manager->id());
            }
        });

        static::creating(function (Model $model): void {
            $manager = app(TenantManager::class);

            if ($manager->hasTenant() && empty($model->tenant_id)) {
                $model->tenant_id = $manager->id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Query helper to bypass tenant scoping deliberately (platform use).
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}
