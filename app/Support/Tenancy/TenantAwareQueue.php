<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;

/**
 * Carries the current tenant into queued jobs.
 *
 * A queue worker serves every courier, so it has no tenant of its own.
 * When a job is pushed inside a tenant (a customer notification, say),
 * the tenant id is written into the job payload. The worker binds that
 * tenant before the job is unserialised and clears it afterwards, so
 * tenant scopes, per courier WhatsApp credentials, branding and the demo
 * courier's "never send" guard all behave exactly as they do in a request.
 */
class TenantAwareQueue
{
    public const PAYLOAD_KEY = 'tenant_id';

    public static function register(Dispatcher $events): void
    {
        Queue::createPayloadUsing(function (): array {
            $tenantId = app(TenantManager::class)->id();

            return $tenantId === null ? [] : [self::PAYLOAD_KEY => $tenantId];
        });

        $events->listen(JobProcessing::class, function (JobProcessing $event): void {
            $manager = app(TenantManager::class);
            $tenantId = $event->job->payload()[self::PAYLOAD_KEY] ?? null;

            if ($tenantId === null) {
                // Sync jobs run inside the request that pushed them, so keep
                // whatever tenant that request already resolved.
                if ($event->connectionName !== 'sync') {
                    $manager->forget();
                }

                return;
            }

            $tenant = Tenant::query()->find($tenantId);

            if ($tenant === null) {
                $manager->forget();

                return;
            }

            $manager->set($tenant);
        });

        $events->listen(
            [JobProcessed::class, JobFailed::class, JobExceptionOccurred::class],
            function (JobProcessed|JobFailed|JobExceptionOccurred $event): void {
                if ($event->connectionName !== 'sync') {
                    app(TenantManager::class)->forget();
                }
            },
        );
    }
}
