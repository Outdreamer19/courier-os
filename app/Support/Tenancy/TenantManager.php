<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;

/**
 * Holds the currently resolved tenant for the lifetime of a request
 * (or a test). Registered as a singleton so every consumer sees the
 * same current tenant.
 */
class TenantManager
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function current(): ?Tenant
    {
        return $this->tenant;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function forget(): void
    {
        $this->tenant = null;
    }
}
