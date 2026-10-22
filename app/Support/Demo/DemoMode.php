<?php

namespace App\Support\Demo;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CentralDomain;
use App\Support\Tenancy\TenantManager;

/**
 * Small helpers for the public demo courier (see config('courieros.demo')).
 *
 * The demo is a normal tenant with public logins, so anything that could
 * reach the outside world (email, WhatsApp, payments) or lock other visitors
 * out (changing a demo login's email or password) is switched off for it.
 */
class DemoMode
{
    /** The fictional courier the demo runs as. */
    public const COURIER_NAME = 'Blue Mahoe Express';

    /**
     * Where prospects open the demo (its login page), or null when the demo
     * is switched off.
     */
    public static function url(string $path = '/login'): ?string
    {
        $subdomain = CentralDomain::demoSubdomain();

        if ($subdomain === null) {
            return null;
        }

        $scheme = Tenant::urlScheme();

        return $scheme.'://'.$subdomain.'.'.config('courieros.central_domain').'/'.ltrim($path, '/');
    }

    /**
     * Whether the tenant bound to the current request is the demo courier.
     */
    public static function active(): bool
    {
        return (bool) app(TenantManager::class)->current()?->isDemo();
    }

    /**
     * The public login emails shown on the demo login page.
     *
     * @return list<string>
     */
    public static function loginEmails(): array
    {
        return array_values(array_filter([
            config('courieros.demo.customer_email'),
            config('courieros.demo.admin_email'),
        ]));
    }

    /**
     * Whether an outbound notification to this notifiable must be dropped.
     *
     * Checks the notifiable's own tenant as well as the bound tenant, so a
     * queued notification processed by a worker (where no tenant is bound)
     * is still caught.
     */
    public static function shouldSuppressNotificationTo(mixed $notifiable): bool
    {
        if ($notifiable instanceof User && $notifiable->tenant_id) {
            $tenant = Tenant::query()->find($notifiable->tenant_id);

            if ($tenant?->isDemo()) {
                return true;
            }
        }

        return self::active();
    }

    /**
     * Shared Inertia props for the demo, or null outside it.
     *
     * @return array<string, mixed>|null
     */
    public static function sharedProps(): ?array
    {
        if (! self::active()) {
            return null;
        }

        $central = (string) config('courieros.central_domain');
        $scheme = Tenant::urlScheme();

        return [
            'password' => (string) config('courieros.demo.password'),
            'customer_email' => (string) config('courieros.demo.customer_email'),
            'admin_email' => (string) config('courieros.demo.admin_email'),
            'get_started_url' => "{$scheme}://{$central}/jamaica",
            'platform_name' => (string) config('courieros.name', 'CourierOS'),
        ];
    }
}
