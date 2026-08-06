<?php

namespace App\Support\Tenancy;

/**
 * Answers questions about the configured central domain.
 *
 * "No tenant bound" is not the same thing as "this is the CourierOS platform
 * domain". ResolveTenant also leaves the tenant unbound for hosts it does not
 * recognise at all, which is what lets the app run as a single-courier
 * install (and what lets the test suite and local dev work on localhost).
 * Guards that must only fire on courieros.co itself need this distinction,
 * so the host rules live here rather than being duplicated per middleware.
 */
class CentralDomain
{
    public function host(): string
    {
        return strtolower((string) config('courieros.central_domain'));
    }

    /**
     * Whether the host is the central domain or any subdomain of it.
     */
    public function covers(string $host): bool
    {
        $host = strtolower($host);
        $central = $this->host();

        return $host === $central || str_ends_with($host, '.'.$central);
    }

    /**
     * The left-most label of a host under the central domain, or null for the
     * apex (and for hosts that are not under it at all).
     */
    public function subdomain(string $host): ?string
    {
        $host = strtolower($host);
        $central = $this->host();

        if ($host === $central) {
            return null;
        }

        $suffix = '.'.$central;

        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $label = substr($host, 0, -strlen($suffix));

        return $label === '' ? null : $label;
    }

    /**
     * Whether the host is a CourierOS platform surface: the apex, or one of
     * the reserved subdomains that never resolve to a tenant.
     */
    public function isPlatformHost(string $host): bool
    {
        if (! $this->covers($host)) {
            return false;
        }

        $subdomain = $this->subdomain($host);

        if ($subdomain === null) {
            return true;
        }

        return in_array($subdomain, (array) config('courieros.reserved_subdomains', []), true);
    }
}
