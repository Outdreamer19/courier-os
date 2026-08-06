<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\CentralDomain;
use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Keeps tenant-scoped surfaces off the CourierOS platform domain, and is the
 * mirror image of EnsureCentralContext.
 *
 * Routes in routes/web.php are registered on the shared `web` group with no
 * domain constraint, so they answer on courieros.co too. That is far more
 * dangerous than it sounds: with no tenant bound, BelongsToTenant's global
 * scope deliberately becomes a no-op so platform code can query across
 * tenants, and both EnsureUserBelongsToTenant and EnsureTenantSubscribed
 * short-circuit for the same reason. A tenant admin who authenticated on
 * courieros.co and opened /admin therefore hit controllers that rely entirely
 * on that global scope — Admin\DashboardController counts User, Package and
 * PreAlert with no explicit tenant filter — and saw figures for every tenant
 * on the platform. It also skipped the subscription guard, so a suspended
 * tenant locked out of its own subdomain could still work centrally.
 *
 * The check is on the platform *host*, not merely on the absence of a tenant:
 * ResolveTenant also leaves the tenant unbound for hosts it does not
 * recognise, which is what lets CourierOS run as a single-courier install.
 * Blocking those would break that mode for no security gain.
 *
 * 404 rather than 403, matching EnsureCentralContext: on the platform domain
 * a tenant's console should not appear to exist.
 */
class EnsureTenantContext
{
    public function __construct(
        private readonly TenantManager $tenants,
        private readonly CentralDomain $central,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->tenants->hasTenant()) {
            return $next($request);
        }

        if ($this->central->isPlatformHost($request->getHost())) {
            throw new NotFoundHttpException;
        }

        return $next($request);
    }
}
