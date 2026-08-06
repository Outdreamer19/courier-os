import { createInertiaApp, usePage } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const fallbackName = import.meta.env.VITE_APP_NAME || 'CourierOS';

/**
 * The browser tab title has to follow the tenant, not the build.
 *
 * VITE_APP_NAME is baked in at build time, so using it directly puts
 * "CourierOS" in the title bar of every tenant's own site — which defeats the
 * point of a white-label product. `brand.name` is shared on every Inertia
 * response by HandleInertiaRequests and resolves to the tenant's name when a
 * tenant is bound, falling back to the platform name in central context.
 *
 * usePage() returns a module-level reactive singleton, so it is safe to read
 * here even though this runs outside a component's setup().
 */
const brandName = (): string => {
    try {
        return usePage().props?.brand?.name || fallbackName;
    } catch {
        return fallbackName;
    }
};

createInertiaApp({
    title: (title) => {
        const name = brandName();

        return title ? `${title} - ${name}` : name;
    },
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('public/'):
                return PublicLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            // Everything under central/ is the CourierOS platform itself, not a
            // tenant's portal. That includes central/admin/* (the platform
            // console), which brings its own PlatformLayout — previously it
            // fell through to the default and got wrapped in the tenant
            // customer sidebar, complete with "My shipping address".
            case name.startsWith('central/'):
                return null;
            default:
                return AppLayout;
        }
    },
    progress: {
        color: 'oklch(0.74 0.13 80)',
        includeCSS: true,
        showSpinner: true,
    },
});

initializeTheme();
initializeFlashToast();
