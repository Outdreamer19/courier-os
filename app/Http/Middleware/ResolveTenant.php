<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\CentralDomain;
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
 * subdomains) bind no tenant and operate in the platform context. The host
 * rules themselves live in CentralDomain so the guards that need to tell a
 * platform host apart from an unrecognised one agree with this.
 */
class ResolveTenant
{
    public function __construct(
        private readonly TenantManager $tenants,
        private readonly CentralDomain $central,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        // Custom domain takes precedence: a host that is not under the central
        // domain may belong to a tenant via its custom_domain.
        if (! $this->central->covers($host)) {
            $tenant = Tenant::query()->where('custom_domain', $host)->first();

            if (! $tenant) {
                // Unknown host entirely; treat as central so marketing still
                // works on arbitrary local hosts in dev — and clear any
                // previously bound tenant from an earlier request in-process.
                $this->tenants->forget();

                return $next($request);
            }

            return $this->bindAndContinue($tenant, $request, $next);
        }

        // Apex or reserved subdomain → central context, no tenant.
        if ($this->central->isPlatformHost($host)) {
            $this->tenants->forget();

            return $next($request);
        }

        $tenant = Tenant::query()
            ->where('subdomain', $this->central->subdomain($host))
            ->first();

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
}
