<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnsureAdminPermission
{
    /**
     * Guard admin routes by permission key.
     *
     * Usage: ->middleware('admin.permission:manage_admins')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAdminPermission($permission)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
