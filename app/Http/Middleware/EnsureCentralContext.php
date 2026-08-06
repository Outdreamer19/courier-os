<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Restricts a route to the CourierOS central domain.
 *
 * Routes in routes/central.php are registered on the shared `web` group, so
 * without this they answer on every tenant subdomain too. That meant a
 * tenant's own customers could reach /signup on their courier's site and be
 * pitched on starting a competing courier platform — the CourierOS brand,
 * pricing and all — which rather defeats the point of a white-label product.
 *
 * 404 rather than 403: on a tenant host these routes should not appear to
 * exist at all.
 */
class EnsureCentralContext
{
    public function __construct(private readonly TenantManager $tenants) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->tenants->hasTenant()) {
            throw new NotFoundHttpException;
        }

        return $next($request);
    }
}
