import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { BrandConfig } from '@/types/auth';

/**
 * The active courier's branding, from the shared Inertia props.
 *
 * Every surface below the marketing site is served to whichever tenant owns
 * the host, so no page may name a courier literally. `brand` is resolved by
 * TenantConfig — the tenant on a tenant host, the app name centrally — and
 * this wraps the null-handling so pages can just interpolate `name`.
 */
export function useBrand() {
    const page = usePage();

    const brand = computed(
        () => (page.props?.brand as BrandConfig | undefined) ?? null,
    );

    const name = computed(() => brand.value?.name ?? 'CourierOS');

    return { brand, name };
}
