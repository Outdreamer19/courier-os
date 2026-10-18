/**
 * Chart palette for the courier portal.
 *
 * The lead series always uses the courier's own brand colour (read from the
 * --primary custom property that useTenantTheme sets), so every courier's
 * dashboard looks like theirs. Supporting series use a clean, saturated set
 * that sits well on white: indigo, sky, emerald, amber, rose and slate.
 */

export const chartBarColors = [
    '#6366F1', // indigo
    '#0EA5E9', // sky
    '#10B981', // emerald
    '#F59E0B', // amber
    '#F43F5E', // rose
    '#94A3B8', // slate
];

/** Pre-alert lifecycle, in PreAlertStatus order. */
export const chartStatusColors = [
    '#6366F1', // submitted
    '#0EA5E9', // under review
    '#F59E0B', // matched to package
    '#F43F5E', // issue found
    '#10B981', // completed
    '#CBD5E1', // cancelled
];

export const chartGridColor = '#EEF1F6';
export const chartTickColor = '#94A3B8';

const FALLBACK_PRIMARY = 'rgb(29, 78, 216)';

/**
 * Resolve a CSS custom property (e.g. --primary) to an rgb() string the
 * canvas understands, whatever format it was written in (hex, hsl, mix).
 */
export function resolveCssColor(
    variable: string,
    fallback = FALLBACK_PRIMARY,
): string {
    if (typeof document === 'undefined') {
        return fallback;
    }

    const probe = document.createElement('span');
    probe.style.color = `var(${variable})`;
    probe.style.display = 'none';
    document.body.appendChild(probe);
    const resolved = getComputedStyle(probe).color;
    probe.remove();

    return resolved && resolved !== '' ? resolved : fallback;
}

/** rgb()/rgba()/#hex to an rgba() string at the given alpha. */
export function withAlpha(color: string, alpha: number): string {
    if (color.startsWith('#')) {
        const value = color.replace('#', '');
        const r = parseInt(value.substring(0, 2), 16);
        const g = parseInt(value.substring(2, 4), 16);
        const b = parseInt(value.substring(4, 6), 16);

        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    const parts = color.match(/[\d.]+/g);

    if (!parts || parts.length < 3) {
        return color;
    }

    return `rgba(${parts[0]}, ${parts[1]}, ${parts[2]}, ${alpha})`;
}

/** Shared tooltip look: a small dark card with soft corners. */
export const chartTooltip = {
    backgroundColor: '#0F172A',
    titleColor: '#F8FAFC',
    bodyColor: '#E2E8F0',
    padding: 10,
    cornerRadius: 10,
    displayColors: false,
    titleFont: { weight: 600 as const, size: 12 },
    bodyFont: { size: 12 },
};

export const prefersReducedMotion = (): boolean =>
    typeof window !== 'undefined' &&
    window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
