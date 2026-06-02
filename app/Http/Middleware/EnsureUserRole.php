<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnsureUserRole
{
    /**
     * Guard a route or group against users without one of the allowed roles.
     *
     * Usage: ->middleware('role:admin') or ->middleware('role:admin,customer')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
