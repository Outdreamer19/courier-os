<?php

namespace App\Http\Controllers\Central\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request): Response
    {
        $tenants = Tenant::query()
            ->withCount('subscriptions')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Tenant $tenant) => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'custom_domain' => $tenant->custom_domain,
                'status' => $tenant->status,
                'currency' => $tenant->currency,
                'created_at' => $tenant->created_at->toDateString(),
            ]);

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
