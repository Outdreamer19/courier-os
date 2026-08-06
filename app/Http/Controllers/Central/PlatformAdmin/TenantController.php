<?php

namespace App\Http\Controllers\Central\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Platform\PlatformStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request, PlatformStatsService $stats): Response
    {
        // Usage/health figures for every tenant, computed in a handful of
        // grouped queries rather than one query per row.
        $health = collect($stats->tenantHealth(PHP_INT_MAX))->keyBy('id');

        $tenants = Tenant::query()
            ->withCount('subscriptions')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Tenant $tenant) use ($health) {
                $row = $health->get($tenant->id, []);

                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'subdomain' => $tenant->subdomain,
                    'custom_domain' => $tenant->custom_domain,
                    'url' => $row['url'] ?? null,
                    'status' => $tenant->status,
                    'currency' => $tenant->currency,
                    'subscription_status' => $row['subscription_status'] ?? 'none',
                    'customers' => $row['customers'] ?? 0,
                    'packages' => $row['packages'] ?? 0,
                    'packages_30d' => $row['packages_30d'] ?? 0,
                    'last_activity_human' => $row['last_activity_human'] ?? null,
                    'health' => $row['health'] ?? 'onboarding',
                    'created_at' => $tenant->created_at->toDateString(),
                ];
            });

        return Inertia::render('central/admin/Tenants', [
            'tenants' => $tenants,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['suspend', 'activate'])],
        ]);

        $tenant->forceFill([
            'status' => $validated['action'] === 'suspend'
                ? Tenant::STATUS_SUSPENDED
                : Tenant::STATUS_ACTIVE,
        ])->save();

        $message = $validated['action'] === 'suspend'
            ? "{$tenant->name} has been suspended."
            : "{$tenant->name} has been reactivated.";

        return back()->with('success', $message);
    }
}
