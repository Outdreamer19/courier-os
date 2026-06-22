<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Guards against cross-tenant access: an authenticated user may only act
 * within their own tenant. If the resolved tenant (from the subdomain)
 * does not match the user's tenant_id, access is forbidden.
 *
 * Platform owners (no tenant_id) are allowed through in central context
 * but never into a tenant subdomain they don't belong to.
 */
class EnsureUserBelongsToTenant
{
    public function __construct(private readonly TenantManager $tenants) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = $this->tenants->current();

        // No authenticated user, or no tenant context: nothing to enforce here.
        if (! $user || ! $tenant) {
            return $next($request);
        }

        // Platform owners do not belong to any tenant and must not roam into
        // tenant subdomains.
        if ($user->isPlatformOwner() || (int) $user->tenant_id !== (int) $tenant->id) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'You do not have access to this courier account.');
        }

        return $next($request);
    }
}
