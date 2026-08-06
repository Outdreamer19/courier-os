<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PreAlert;
use App\Models\User;
use App\Support\CustomerWarehouseAddress;
use App\Support\Tenancy\TenantConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Customer dashboard entry point, and the post-login landing page for
     * every role. Tenant admins are bounced to the tenant admin dashboard and
     * the CourierOS platform owner is bounced to the platform console — only
     * customers actually render this page.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // The platform owner belongs to no tenant, so none of the customer
        // portal data below means anything to them. Send them to the console
        // that does: revenue, tenant health, and signups.
        if ($user instanceof User && $user->isPlatformOwner()) {
            return redirect()->route('central.platform.dashboard');
        }

        if ($user instanceof User && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $profile = $user?->customerProfile;
        $warehouse = CustomerWarehouseAddress::forUser($user);

        $recentPreAlerts = $user
            ?->preAlerts()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (PreAlert $preAlert) => [
                'id' => $preAlert->id,
                'merchant_name' => $preAlert->merchant_name,
                'status' => $preAlert->status->value,
                'status_label' => $preAlert->status->label(),
                'created_at' => $preAlert->created_at?->toIso8601String(),
            ]) ?? collect();

        $recentPackages = $user
            ?->packages()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Package $package) => [
                'id' => $package->id,
                'package_reference' => $package->package_reference,
                'status' => $package->status->value,
                'status_label' => $package->status->label(),
                'payment_status' => $package->payment_status->value,
                'amount_due' => (float) $package->amount_due,
            ]) ?? collect();

        $amountDue = $user
            ? (float) $user->packages()->outstandingPayment()->sum('amount_due')
            : 0;

        return Inertia::render('Dashboard', [
            'profile' => $profile ? [
                'customer_reference' => $profile->customer_reference,
                'phone' => $profile->phone,
                'whatsapp_number' => $profile->whatsapp_number,
                'jamaica_address' => $profile->jamaica_address,
                'parish' => $profile->parish,
            ] : null,
            'warehouse' => $warehouse,
            'stats' => [
                'active_packages' => $user?->packages()->active()->count() ?? 0,
                'pre_alerts' => $user?->preAlerts()->count() ?? 0,
                'amount_due' => $amountDue,
                'currency' => app(TenantConfig::class)->currency(),
            ],
            'recentPreAlerts' => $recentPreAlerts,
            'recentPackages' => $recentPackages,
        ]);
    }
}
