import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { BrandConfig } from '@/types/auth';

const HEX_COLOR = /^#[0-9A-Fa-f]{6}$/;

/** CSS custom properties driven by the tenant's chosen primary colour. */
const PRIMARY_VARS = [
    '--primary',
    '--sidebar-primary',
    '--ring',
    '--sidebar-ring',
];
const PRIMARY_FOREGROUND_VARS = [
    '--primary-foreground',
    '--sidebar-primary-foreground',
];

/** CSS custom properties driven by the tenant's chosen accent colour. */
const ACCENT_VARS = ['--accent', '--sidebar-accent'];
const ACCENT_FOREGROUND_VARS = [
    '--accent-foreground',
    '--sidebar-accent-foreground',
];

/**
 * Applies the current tenant's primary/accent brand colours as inline CSS
 * custom property overrides on the document root, so the authenticated
 * portal reflects the tenant's own branding instead of the default
 * CourierOS navy/red theme (defined in app.css).
 *
 * Inline styles on `<html>` take precedence over both the `:root` and
 * `.dark` rules in app.css, so this works regardless of light/dark mode.
 * When a tenant hasn't set a colour, the override is removed and the
 * stylesheet default applies. Central/platform pages have no tenant bound,
 * so `brand.primary_color`/`accent_color` are always null there and this
 * composable is a no-op.
 */
export function useTenantTheme(): void {
    const page = usePage();
    const brand = computed(() => page.props.brand as BrandConfig | undefined);

    watch(
        brand,
        (value) => {
            applyColor(
                value?.primary_color,
                PRIMARY_VARS,
                PRIMARY_FOREGROUND_VARS,
            );
            applyColor(
                value?.accent_color,
                ACCENT_VARS,
                ACCENT_FOREGROUND_VARS,
            );
        },
        { immediate: true },
    );
}

function applyColor(
    hex: string | null | undefined,
    colorVars: string[],
    foregroundVars: string[],
): void {
    if (typeof document === 'undefined') {
        return;
    }

    const root = document.documentElement;

    if (!hex || !HEX_COLOR.test(hex)) {
        [...colorVars, ...foregroundVars].forEach((name) =>
            root.style.removeProperty(name),
        );

        return;
    }

    const foreground = contrastingTextColor(hex);

    colorVars.forEach((name) => root.style.setProperty(name, hex));
    foregroundVars.forEach((name) => root.style.setProperty(name, foreground));
}

/**
 * Picks near-black or near-white text based on the relative luminance of a
 * hex colour (WCAG relative luminance formula), so text stays legible
 * against whatever colour a tenant picks.
 */
function contrastingTextColor(hex: string): string {
    const channel = (start: number) =>
        parseInt(hex.slice(start, start + 2), 16) / 255;
    const linear = (value: number) =>
        value <= 0.03928 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;

    const luminance =
        0.2126 * linear(channel(1)) +
        0.7152 * linear(channel(3)) +
        0.0722 * linear(channel(5));

    return luminance > 0.5 ? 'hsl(240 10% 6%)' : 'hsl(0 0% 100%)';
}
