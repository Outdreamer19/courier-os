<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Demo\DemoMode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the public demo courier usable for the next visitor.
 *
 * Visitors can do almost everything a real courier or customer can (create
 * pre-alerts, receive packages, change statuses, edit rates) because the
 * demo is wiped nightly. What is blocked: anything that sends money or
 * messages out, changes how someone logs in, or touches billing and branding.
 */
class GuardDemoTenant
{
    public const MESSAGE = 'This is a demo, so that action is switched off. Everything else works, and the demo resets every night.';

    /**
     * Route names (wildcards allowed) that are read only on the demo.
     *
     * @var list<string>
     */
    private const BLOCKED_ROUTES = [
        // Accounts and sign in
        'register.store',
        'password.email',
        'password.update',
        'verification.send',
        'two-factor.*',
        'passkey.store',
        'passkey.destroy',
        'profile.update',
        'profile.destroy',
        'user-password.update',
        // Courier settings that reach outside the demo
        'branding.*',
        'admin.admin-users.store',
        'admin.admin-users.update',
        'admin.packages.billing.*',
        'admin.platform-support.store',
        'admin.platform-support.messages.store',
        'admin.platform-support.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || ! DemoMode::active()) {
            return $next($request);
        }

        if ($this->isBlocked($request)) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json(['message' => self::MESSAGE], Response::HTTP_FORBIDDEN);
            }

            // Toast for the portal pages, plus the classic flash for anything
            // that reads it from the session.
            Inertia::flash('toast', ['type' => 'info', 'message' => self::MESSAGE]);

            return back()->with('error', self::MESSAGE);
        }

        return $next($request);
    }

    private function isBlocked(Request $request): bool
    {
        $name = (string) $request->route()?->getName();

        if ($name !== '' && Str::is(self::BLOCKED_ROUTES, $name)) {
            return true;
        }

        // Admins may edit customers, but not the public demo logins: changing
        // their email or suspending them would lock out the next visitor.
        if ($name === 'admin.customers.update') {
            $customer = $request->route('customer');

            if (! $customer instanceof User && is_scalar($customer)) {
                $customer = User::query()->find($customer);
            }

            if ($customer instanceof User && in_array($customer->email, DemoMode::loginEmails(), true)) {
                return true;
            }
        }

        return false;
    }
}
