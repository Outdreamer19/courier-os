<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Restricts a route to the CourierOS platform owner operating in the
 * central context (no tenant bound). Tenant users, even tenant owners,
 * are forbidden.
 */
class EnsurePlatformOwner
{
    public function __construct(private readonly TenantManager $tenants) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isPlatformOwner() || $this->tenants->hasTenant()) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Platform access only.');
        }

        return $next($request);
    }
}
