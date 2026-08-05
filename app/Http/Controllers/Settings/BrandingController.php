<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\BrandingUpdateRequest;
use App\Support\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manages the tenant's branding settings: business name, currency, primary
 * colour, and logo upload. Only accessible to users with the
 * manage_system_settings permission (i.e. tenant owners).
 */
class BrandingController extends Controller
{
    public function __construct(private readonly TenantManager $tenants) {}

    /**
     * Show the branding settings page for the current tenant.
     */
    public function edit(): Response
    {
        $tenant = $this->tenants->current();

        abort_unless($tenant !== null, 404);

        return Inertia::render('settings/Branding', [
            'tenant' => [
                'name' => $tenant->name,
                'currency' => $tenant->currency,
                'brand_primary_color' => $tenant->brand_primary_color,
                'brand_accent_color' => $tenant->brand_accent_color,
                'logo_path' => $tenant->logo_path
                    ? Storage::disk('public')->url($tenant->logo_path)
                    : null,
            ],
            'currencies' => [
                'USD' => 'US Dollar (USD)',
                'JMD' => 'Jamaican Dollar (JMD)',
                'CAD' => 'Canadian Dollar (CAD)',
                'EUR' => 'Euro (EUR)',
                'GBP' => 'British Pound (GBP)',
                'TTD' => 'Trinidad & Tobago Dollar (TTD)',
                'BBD' => 'Barbadian Dollar (BBD)',
                'KYD' => 'Cayman Islands Dollar (KYD)',
                'XCD' => 'Eastern Caribbean Dollar (XCD)',
            ],
        ]);
    }

    /**
     * Update the tenant's branding fields (name, currency, primary colour).
     */
    public function update(BrandingUpdateRequest $request): RedirectResponse
    {
        $tenant = $this->tenants->current();

        abort_unless($tenant !== null, 404);

        $validated = $request->validated();

        // Treat an empty string as null for the optional colour fields.
        $validated['brand_primary_color'] = filled($validated['brand_primary_color'])
            ? $validated['brand_primary_color']
            : null;

        $validated['brand_accent_color'] = filled($validated['brand_accent_color'] ?? null)
            ? $validated['brand_accent_color']
            : null;

        $tenant->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Branding settings updated.')]);

        return to_route('branding.edit');
    }

    /**
     * Store an uploaded logo for the current tenant.
     */
    public function uploadLogo(Request $request): RedirectResponse
    {
        $tenant = $this->tenants->current();

        abort_unless($tenant !== null, 404);

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,webp,svg', 'max:2048'],
        ]);

        // Delete the previous logo if one exists.
        if ($tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
        }

        $path = $request->file('logo')->store("tenants/{$tenant->id}", 'public');

        $tenant->update(['logo_path' => $path]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Logo updated.')]);

        return to_route('branding.edit');
    }

    /**
     * Remove the current tenant's logo.
     */
    public function destroyLogo(): RedirectResponse
    {
        $tenant = $this->tenants->current();

        abort_unless($tenant !== null, 404);

        if ($tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
            $tenant->update(['logo_path' => null]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Logo removed.')]);

        return to_route('branding.edit');
    }
}
