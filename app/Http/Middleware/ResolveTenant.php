<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves the current tenant from the request host and binds it into the
 * TenantManager so the BelongsToTenant global scope can isolate data.
 *
 * Hosts on the central domain (apex, www, app, and other reserved
 * subdomains) bind no tenant and operate in the platform context.
 */
class ResolveTenant
{
    public function __construct(private readonly TenantManager $tenants) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $central = strtolower((string) config('courieros.central_domain'));
        $reserved = (array) config('courieros.reserved_subdomains', []);

        // Custom domain takes precedence: a host that is not under the central
        // domain may belong to a tenant via its custom_domain.
        if (! $this->isUnderCentralDomain($host, $central)) {
            $tenant = Tenant::query()->where('custom_domain', $host)->first();

            if (! $tenant) {
                // Unknown host entirely; let it fall through as central so the
                // app/marketing still works on arbitrary local hosts in dev.
                return $next($request);
            }

            return $this->bindAndContinue($tenant, $request, $next);
        }

        $subdomain = $this->extractSubdomain($host, $central);

        // Apex or reserved subdomain → central context, no tenant.
        if ($subdomain === null || in_array($subdomain, $reserved, true)) {
            return $next($request);
        }

        $tenant = Tenant::query()->where('subdomain', $subdomain)->first();

        if (! $tenant) {
            throw new NotFoundHttpException('Unknown tenant.');
        }

        return $this->bindAndContinue($tenant, $request, $next);
    }

    private function bindAndContinue(Tenant $tenant, Request $request, Closure $next): Response
    {
        if (! $tenant->isActive()) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                'This courier account is not currently active.'
            );
        }

        $this->tenants->set($tenant);

        return $next($request);
    }

    private function isUnderCentralDomain(string $host, string $central): bool
    {
        return $host === $central || str_ends_with($host, '.'.$central);
    }

    private function extractSubdomain(string $host, string $central): ?string
    {
        if ($host === $central) {
            return null;
        }

        $suffix = '.'.$central;

        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $label = substr($host, 0, -strlen($suffix));

        // Only treat a single left-most label as the tenant subdomain.
        return $label === '' ? null : $label;
    }
}
