<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Tenancy\CentralDomain;
use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Guards against cross-tenant access: an authenticated user may only act
 * within their own tenant. If the resolved tenant (from the subdomain)
 * does not match the user's tenant_id, access is forbidden.
 *
 * Platform owners (no tenant_id) belong on the CourierOS platform domain
 * only, and never inside a tenant subdomain.
 */
class EnsureUserBelongsToTenant
{
    public function __construct(
        private readonly TenantManager $tenants,
        private readonly CentralDomain $central,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = $this->tenants->current();

        if (! $user) {
            return $next($request);
        }

        // On the platform domain this used to fall straight through, on the
        // grounds that there was no tenant to enforce against. That had it
        // backwards: with no tenant bound the BelongsToTenant global scope is
        // switched off, so a tenant user reaching this point saw unscoped
        // data. Only the platform owner has any business on courieros.co.
        //
        // Hosts that simply are not recognised (single-courier installs, local
        // dev) are left alone — there is no platform there to protect.
        if (! $tenant) {
            if ($user instanceof User && $user->isPlatformOwner()) {
                return $next($request);
            }

            if ($this->central->isPlatformHost($request->getHost())) {
                return $this->sendToOwnTenant($request, $user);
            }

            return $next($request);
        }

        // Platform owners do not belong to any tenant and must not roam into
        // tenant subdomains.
        if ($user->isPlatformOwner() || (int) $user->tenant_id !== (int) $tenant->id) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'You do not have access to this courier account.');
        }

        return $next($request);
    }

    /**
     * Bounce a tenant user off the platform domain and onto their courier's
     * own site.
     *
     * The central session is discarded rather than left dangling: it grants
     * nothing now that the tenant surfaces 404 there, and sessions are
     * host-scoped (SESSION_DOMAIN is deliberately null) so it could not have
     * followed them to the tenant host anyway.
     */
    private function sendToOwnTenant(Request $request, mixed $user): Response
    {
        $tenant = $user instanceof User ? $user->tenant : null;

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (! $tenant) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'You do not have access to this courier account.');
        }

        return redirect()->away($tenant->url('/login'));
    }
}
