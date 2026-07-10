<?php

namespace App\Http\Middleware;

use App\Models\WarehouseAddress;
use App\Support\Tenancy\TenantConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $brand = app(TenantConfig::class);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'role' => $user->role,
                    'status' => $user->status,
                    'is_admin' => $user->isAdmin(),
                    'is_owner' => $user->isOwner(),
                    'is_customer' => $user->isCustomer(),
                    'customer_reference' => $user->customerReference(),
                    'admin_permissions' => $user->isAdmin() ? [
                        'manage_admins' => $user->hasAdminPermission('manage_admins'),
                        'view_activity_logs' => $user->hasAdminPermission('view_activity_logs'),
                        'manage_system_settings' => $user->hasAdminPermission('manage_system_settings'),
                        'manage_customers' => $user->hasAdminPermission('manage_customers'),
                        'manage_contact_messages' => $user->hasAdminPermission('manage_contact_messages'),
                        'delete_records' => $user->hasAdminPermission('delete_records'),
                        'manage_billing' => $user->hasAdminPermission('manage_billing'),
                    ] : null,
                ] : null,
            ],
            'brand' => [
                'name' => $brand->name(),
                'currency' => $brand->currency(),
                'logo_path' => $brand->logoPath()
                    ? Storage::disk('public')->url($brand->logoPath())
                    : null,
                'primary_color' => $brand->brandPrimaryColor(),
                'accent_color' => $brand->brandAccentColor(),
                'default_rate_per_lb' => $brand->defaultRatePerLb(),
            ],
            'warehouse' => fn () => $this->activeWarehouseSnapshot(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * Snapshot of the currently active warehouse address for use in the
     * customer portal and shipping address page. Returned as a closure so it
     * is only resolved when a page actually consumes it.
     *
     * @return array<string, mixed>|null
     */
    private function activeWarehouseSnapshot(): ?array
    {
        $warehouse = WarehouseAddress::active();

        if (! $warehouse) {
            return null;
        }

        return [
            'name' => $warehouse->name,
            'address_line_1' => $warehouse->address_line_1,
            'address_line_2' => $warehouse->address_line_2,
            'city' => $warehouse->city,
            'state' => $warehouse->state,
            'zip' => $warehouse->zip,
            'phone' => $warehouse->phone,
            'instructions' => $warehouse->instructions,
            'single_line' => $warehouse->singleLine(),
        ];
    }
}
