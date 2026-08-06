<?php

use App\Http\Middleware\EnsureAdminPermission;
use App\Http\Middleware\EnsureCentralContext;
use App\Http\Middleware\EnsurePlatformOwner;
use App\Http\Middleware\EnsureTenantContext;
use App\Http\Middleware\EnsureTenantSubscribed;
use App\Http\Middleware\EnsureUserBelongsToTenant;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(__DIR__.'/../routes/central.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The app always runs behind a proxy in production (Forge's nginx, and
        // Cloudflare in front of it when the wildcard record is proxied). Without
        // trusted proxies Laravel sees plain HTTP, which breaks secure-cookie
        // issuance and makes generated URLs http:// on an https:// site. The
        // forwarded Host header also feeds ResolveTenant, so tenant resolution
        // depends on this being right.
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'webhooks/invoicefeed',
        ]);

        $middleware->web(prepend: [
            ResolveTenant::class,
        ], append: [
            HandleAppearance::class,
            EnsureUserIsActive::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'central' => EnsureCentralContext::class,
            'tenant' => EnsureTenantContext::class,
            'role' => EnsureUserRole::class,
            'admin.permission' => EnsureAdminPermission::class,
            'active' => EnsureUserIsActive::class,
            'tenant.subscribed' => EnsureTenantSubscribed::class,
            'tenant.member' => EnsureUserBelongsToTenant::class,
            'platform' => EnsurePlatformOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
