<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Ensures the current tenant has an entitlement to use the platform:
 * an active Stripe subscription, an in-window generic trial, or (as a
 * fallback for tenants provisioned before billing) active status.
 *
 * Runs only when a tenant is bound; central/platform routes are never
 * guarded here.
 */
class EnsureTenantSubscribed
{
    public function __construct(private readonly TenantManager $tenants) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenants->current();

        // Central context (no tenant) is not subject to the subscription guard.
        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->isSuspended() || $tenant->isCancelled()) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                'This courier account is not currently active.'
            );
        }

        $entitled = $tenant->subscribed('default')
            || $tenant->onGenericTrial()
            || $tenant->isActive();

        if (! $entitled) {
            throw new HttpException(
                Response::HTTP_PAYMENT_REQUIRED,
                'A subscription is required to access this area.'
            );
        }

        return $next($request);
    }
}
